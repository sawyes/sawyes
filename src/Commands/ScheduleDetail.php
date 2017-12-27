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

            // 详细运行时间表
            $this->insertScheduleDetail($scheduleData);

            $this->info('done!');
        }
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
        $progress = $this->output->createProgressBar(count($scheduleData));

        foreach ($scheduleData as $rows) {

            $scheduleDates = $this->scheduleRuntimeList($rows['expression']);

            $scheduleDetail = [];
            foreach ($scheduleDates as $scheduleDate) {
                $scheduleDetail[] =  [
                    'full_command'  => $rows['full_command'],
                    'short_command' => $rows['short_command'],
                    'expression'    => $rows['expression'],
                    'mutex_name'    => $rows['mutex_name'],
                    'timezone'      => $rows['timezone'],
                    'schedule_date' => $scheduleDate,
                ];
            }

            \DB::connection(config('sawyes.schedule.connection', ''))
                ->table(config('sawyes.schedule.schedule_detail_table', 'schedule_detail'))
                ->insert($scheduleDetail);

            $scheduleInfo = [
                'full_command'        => $rows['full_command'],
                'short_command'       => $rows['short_command'],
                'expression'          => $rows['expression'],
                'without_overlapping' => $rows['without_overlapping'],
                'expires_at'          => $rows['expires_at'],
                'mutex_name'          => $rows['mutex_name'],
                'timezone'            => $rows['timezone'],
                'jobs_total'          => count($scheduleDetail),
                'run_date'            => $this->startDay()->toDateString(),
                'created_at'          => Carbon::now()->toDateTimeString(),
                'updated_at'          => Carbon::now()->toDateTimeString(),
            ];

            \DB::connection(config('sawyes.schedule.connection', ''))
                ->table(config('sawyes.schedule.schedule_info_table', 'schedule'))
                ->insert($scheduleInfo);

            $progress->advance();
        }

        $progress->finish();
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

            $scheduleData[] = $data;

        });

        return $scheduleData;
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
     * @param string $expression
     * @return array
     */
    private function scheduleRuntimeList($expression)
    {
        $flag = true;
        $nth = 0; // 周期
        $scheduleDate = [];
        while ($flag) {

            $commandRunAt = Carbon::instance(CronExpression::factory($expression)
                ->setMaxIterationCount(10000)
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
