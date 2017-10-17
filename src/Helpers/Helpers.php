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