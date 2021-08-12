<?php
namespace ipsrv;

class IPSrvDB {
	public function __construct($filename) {
        if (is_readable($filename) === false) {
            throw new \Exception("File \"$filename\" does not exist or is not readable.");
        }
        $this->fd = @fopen($filename, 'rb');
        if ($this->fd === false) {
            throw new \Exception("Can not open file \"$filename\".");
        }
        $this->filesize = @filesize($filename);
        if ($this->filesize === false) {
            throw new \Exception("Error determining the size of \"$filename\".");
        }
        $this->index_size = unpack('Q', fread($this->fd, 8))[1];
        $this->data_size = unpack('Q', fread($this->fd, 8))[1];
        $this->header_size = unpack('S', fread($this->fd, 2))[1];
        $des_size = $this->filesize - 26 - $this->index_size * 24 - $this->data_size - $this->header_size + 1;

        $this->index = fread($this->fd, $this->index_size * 24);
        $this->data = fread($this->fd, $this->data_size);
        $this->header = explode(",", trim(fread($this->fd, $this->header_size)));
        $this->date = fread($this->fd, 8);
        $this->description = fread($this->fd, $des_size);
	}

	private function ipcmp($a, $b) {
        $a_low = $a[0];
        $a_high = $a[1];
        $b_low = $b[0];
        $b_high = $b[1];
	    if ($a_high == $b_high) {
	        if ($a_low == $b_low) {
	            return 0;
	        } elseif ($a_low > $b_low) {
	            return 1;
	        }
	        return -1;
	    } elseif($a_high > $b_high) {
	        return 1;
	    }
	    return -1;
	}
	
	private function ip_to_int($ip) {
        $high = 0;
	    $low = ip2long($ip);
	    if ($low === false) {
            $in6_addr = inet_pton($ip);
            if ($in6_addr !== false) {
	            for ($i=0; $i<8; $i++) {
	                $high = ($high << 8) + unpack("C", $in6_addr[$i])[1];
	            }
	            for ($i=8; $i<16; $i++) {
	                $low = ($low << 8) + unpack("C", $in6_addr[$i])[1];
	            }
	        }
	    }
	    return array($low, $high);
	}

	public function find($ip) {
	    $ipint = $this->ip_to_int($ip);
	    $start = 0;
	    $mid = 0;
	    $end = ($this->index_size) - 1;
	    while ($start <= $end) {
	        $mid = intval(($start + $end) / 2);
            $_high = substr($this->index, $mid * 24, 8);
            $high = unpack("Q", $_high)[1];
            $_low = substr($this->index, $mid * 24 + 8, 8);
            $low = unpack("Q", $_low)[1];
	        if($this->ipcmp(array($low, $high), $ipint) > 0) {
	            $end = $mid;
	        } elseif($this->ipcmp(array($low, $high), $ipint) < 0) {
	            $start = $mid;
	            if($start == $end - 1) {
        			$_offset = substr($this->index, $mid * 24 + 16, 4);
					$offset = unpack("L", $_offset)[1];
					$_len = substr($this->index, $mid * 24 + 20, 4);
					$len = unpack("L", $_len)[1];
	        	    return substr($this->data, $offset, $len);
	            }
	        } elseif($this->ipcmp(array($low, $high), $ipint) == 0) {
        		$_offset = substr($this->index, $mid * 24 + 16, 4);
				$offset = unpack("L", $_offset)[1];
				$_len = substr($this->index, $mid * 24 + 20, 4);
				$len = unpack("L", $_len)[1];
	            return substr($this->data, $offset, $len);
	        }
	    }
	}

    public function findx($ip) {
        return array_combine($this->header, explode(",", $this->find($ip)));
    }

    public function get_date() {
        return $this->date;
    }

    public function get_header() {
        return $this->header;
    }

    public function get_description() {
        return $this->description;
    }
}
?>
