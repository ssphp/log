<?php

namespace ssphp\Formatter;

class Json implements FormatterInterface
{
	public function format(array $data)
	{
		return date("Y/m/d H:i:s.u") . " " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
	}
}