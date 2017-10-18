<?php   
if(! function_exists('trimArray')) {
    /**
     * trim
     *
     * @param array|string $datas
     *
     * @return array|string
     *
     */
    function trimArray($datas)
    {
        if (is_numeric($datas) || is_bool($datas) || is_resource($datas))
            return $datas;
        elseif (! is_array($datas))
            return trim($datas);

        return array_map('trimArray', $datas);
    }
}

if(! function_exists('assetWithVersion')) {
    /**
     * 给资源文件生成版本信息, 版本依据文件修改时间
     * 
     * @param string $path
     * 
     * @return string
     */
    function assetWithVersion($path)
    {
        $filePath = public_path($path);

        if (\File::exists($filePath)) {
            $time = \File::lastModified($path);
            return asset($path) . '?v=' . $time;
        }
        return asset($path);
    }
}