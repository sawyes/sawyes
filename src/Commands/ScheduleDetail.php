<?php

namespace Sawyes\Commands;

use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * see tomorrow schedule list, insert data in database
     *  php artisan schedule:detail --database=true --start=tomorrow
     *
     * @var string
     */
    protected $signature = 'schedule:detail {--start=} {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show command run time lists run in one day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @throws \Exception
     * @return mixed
     */
    public function handle(Schedule $schedule)
    {
        $events = $schedule->events();

        $scheduleData = $this->parseScheduleData($events);

        $this->line('Runing schedule list date : <comment>' . $this->startDay()->startOfDay() .'</comment>');

        $this->printSchedule($scheduleData);

        if ($this->hasOption('database') && $this->option('database')) {

            $this->info('inserting data into database...');

            // 主要信息
            $this->insertScheduleInfo($scheduleData);

            // 详细运行时间表
            $this->insertScheduleDetail($scheduleData);

            $this->info('done!');
        }
    }

    /**
     * schedule list info
     *
     * @param array $scheduleData
     *
     */
    public function insertScheduleInfo($scheduleData)
    {

        $insertData = collect($scheduleData)->reduce(function($carry, $item){
            $data = [
                'full_command'        => $item['full_command'],
                'short_command'       => $item['short_command'],
                'expression'          => $item['expression'],
                'without_overlapping' => $item['without_overlapping'],
                'expires_at'          => $item['expires_at'],
                'mutex_name'          => $item['mutex_name'],
                'timezone'            => $item['timezone'],
                'jobs_total'          => count($item['schedule_date']),
                'run_date'            => $this->startDay()->toDateString(),
                'created_at'          => Carbon::now()->toDateTimeString(),
                'updated_at'          => Carbon::now()->toDateTimeString(),
            ];
            $carry[] = $data;

            return $carry;
        });

        \DB::connection(config('sawyes.schedule.connection', ''))
            ->table(config('sawyes.schedule.schedule_info_table', 'schedule'))
            ->insert($insertData);
    }

    /**
     * schedule detail
     *
     * @param array $scheduleData
     *
     * @throws \Exception
     *
     */
    public function insertScheduleDetail($scheduleData)
    {
        $header = [
            'full_command' ,
            'short_command' ,
            'expression' ,
            'mutex_name' ,
            'timezone' ,
            'schedule_date' ,
        ];

        $scheduleDetail = $this->getFormatData($header, $scheduleData);

        \DB::connection(config('sawyes.schedule.connection', ''))
            ->table(config('sawyes.schedule.schedule_detail_table', 'schedule_detail'))
            ->insert($scheduleDetail);
    }

    /**
     * to print data on console screen
     *
     * @param array $scheduleData
     * @return mixed
     */
    public function printSchedule($scheduleData)
    {
        if (empty($scheduleData)) {
            $this->line('None schedule lists to print');
            return;
        }

        $header = [
            'short_command' ,
            'expression' ,
            'mutex_name' ,
            'without_overlapping' ,
            'expires_at',
            'timezone',
            'next_time' ,
        ];

        $printData = collect($scheduleData)->reduce(function($carry, $item) use($header) {

            $data = [];

            foreach ($header as $field) {
                $data[$field] = $item[$field];
            }
            $carry[] = $data;

            return $carry;
        });

        $this->table($header, $printData);
    }

    /**
     * parse command detail and run time lists in on day
     *
     * @param \Illuminate\Console\Scheduling\Event[] $events
     *
     * @return array
     *
     */
    public function parseScheduleData($events)
    {
         $scheduleData = [];

         collect($events)->each(function(Event $event) use(&$scheduleData) {
            
            $data = [
                'full_command' => $event->buildCommand(),
                'short_command' => $this->getShortCommand($event->buildCommand()),
                'expression' => $event->expression,
                'timezone' => $event->timezone ? $event->timezone: date_default_timezone_get(),
                'mutex_name' => $event->mutexName(),
                'next_time' => $event->nextRunDate(),
                'without_overlapping' => $event->withoutOverlapping ? 'true' : 'false', // Do not allow the event to overlap each other.
                'expires_at' => $event->expiresAt, // 多少分钟后超时运行
                // 'runsInMaintenanceMode' => $event->runsInMaintenanceMode(),//维护模式
            ];

            $data['schedule_date'] = $this->scheduleRuntimeList($event);
            $scheduleData[] = $data;
        });

        return $scheduleData;
    }

    /**
     * getdata by given header
     *
     * @param array $header
     * @param array $data
     * @throws \Exception
     * @return mixed
     *
     */
    private function getFormatData($header = [], $data =[])
    {
        if (empty($header)) {
            throw new \Exception('Error!');
        }

        $formatData = collect($data)->reduce(function($carry, $item) use($header) {
            foreach ($item['schedule_date'] as $scheduleDate) {
                $row = collect($item)->only($header)->toArray();

                $row['schedule_date'] = $scheduleDate;

                $carry[] = $row;
            }

            return $carry;
        });

        return $formatData;
    }

    /**
     * Get the "short" command
     * Remove php binary, "artisan" and std output from the command string
     *
     * @param string $command
     * @return string
     */
    private function getShortCommand($command)
    {
        $command = substr($command, 0, strpos($command, '>'));
        $command = trim(str_replace([PHP_BINARY, 'artisan', '\'', '"'], '', $command));
        return $command;
    }

    /**
     * a list about command run at which time in one day
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @return array
     */
    private function scheduleRuntimeList($event)
    {
        $flag = true;
        $nth = 0; // 周期
        $scheduleDate = [];
        while ($flag) {

            $commandRunAt = Carbon::instance(CronExpression::factory($event->getExpression())
                ->getNextRunDate($this->startDay(), $nth, $allowCurrentDate=false));

            if ($commandRunAt > $this->endDay() || $nth >= 1440) {
                $flag = false;
            } else {
                $scheduleDate[] = $commandRunAt->toDateTimeString();

            }

            $nth++;
        }

        return $scheduleDate;
    }

    /**
     * 返回开始时间
     *
     * @return Carbon
     *
     */
    private function startDay()
    {
        if ($this->hasOption('start') && $this->option('start')) {
            return Carbon::parse($this->option('start'));
        }

        return Carbon::now();
    }

    /**
     * 返回结束时间
     *
     * @return Carbon
     *
     */
    private function endDay()
    {
        return $this->startDay()->endOfDay();
    }
}
