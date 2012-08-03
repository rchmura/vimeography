<?php

class Vimeography_Helpers
{
	/**
	 * Converts the video's duration in seconds to the MM:SS format.
	 * 
	 * @access public
	 * @param mixed $seconds
	 * @return void
	 */
	public function seconds_to_minutes($seconds)
	{
	    /// get minutes
	    $minResult = floor($seconds/60);
	    	   
	    /// if minutes is between 0-9, add a "0" --> 00-09
	    if($minResult < 10){$minResult = 0 . $minResult;}

	    /// get sec
	    $secResult = ($seconds/60 - $minResult)*60;
	   
	    /// if secondes is between 0-9, add a "0" --> 00-09
	    if($secResult < 10){$secResult = 0 . $secResult;}
	   
	    /// return result
	    return $minResult . ":" . $secResult;
	}
		
	/**
	 * Truncate strings to defined limit.
	 * Original PHP code by Chirp Internet: www.chirp.com.au 
	 * 
	 * @access public
	 * @param mixed $string
	 * @param mixed $limit
	 * @param string $break (default: " ")
	 * @param string $pad (default: "...")
	 * @return void
	 */
	public function truncate($string, $limit, $break = ' ', $pad = '...') 
	{ 
		// return with no change if string is shorter than $limit 
		if (strlen($string) <= $limit)
			return $string;
			 
		$string = substr($string, 0, $limit);
		 
		if (false !== ($breakpoint = strrpos($string, $break)))
		{
			$string = substr($string, 0, $breakpoint); 
		}
		
		return $string . $pad;
	}
	
	/**
	 * Restore HTML tags to truncated strings.
	 * Original PHP code by Chirp Internet: www.chirp.com.au
	 *
	 * @access public
	 * @param mixed $input
	 * @return void
	 */
	public function restore_tags($input) 
	{ 
		$opened = array(); 
		// loop through opened and closed tags in order 
		if(preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches)) 
		{
			foreach($matches[1] as $tag) 
			{ 
				if(preg_match("/^[a-z]+$/i", $tag, $regs)) 
				{ 
					// a tag has been opened 
					if(strtolower($regs[0]) != 'br') $opened[] = $regs[0]; 
				}
				elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs)) 
				{ 
					// a tag has been closed 
					unset($opened[array_pop(array_keys($opened, $regs[1]))]); 
				} 
			} 
		}
		// close tags that are still open 
		if($opened) 
		{
			$tagstoclose = array_reverse($opened); 
			foreach($tagstoclose as $tag) $input .= "</$tag>"; 
		}
		return $input; 
	}
}