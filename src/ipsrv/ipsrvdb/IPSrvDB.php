<?php
namespace ipsrv\ipsrvdb;

class IPSrvDB {
    public function __construct($filename) {
        if (is_readable($filename) === false) {
            throw new \Exception("File \"$filename\" does not exist or is not readable.");
        }
        $this->fd = @fopen($filename, "rb");
        if ($this->fd === false) {
            throw new \Exception("Can not open file \"$filename\".");
        }
        $this->filesize = @filesize($filename);
        if ($this->filesize === false) {
            throw new \Exception("Error determining the size of \"$filename\".");
        }
        $this->index_size = $this->get_64bit_value(fread($this->fd, 8));
        $this->data_size = $this->get_64bit_value(fread($this->fd, 8));
        $this->header_size = unpack("S", fread($this->fd, 2))[1];
        $des_size = $this->filesize - 26 - $this->index_size * 24 - $this->data_size - $this->header_size + 1;

        $this->index = fread($this->fd, $this->index_size * 24);
        $this->data = fread($this->fd, $this->data_size);
        $this->header = explode(",", trim(fread($this->fd, $this->header_size)));
        $this->date = fread($this->fd, 8);
        $this->description = fread($this->fd, $des_size);
        fclose($this->fd);
    }

    private function is_32bit() {
        return PHP_INT_SIZE === 4;
    }

    private function get_64bit_value($raw) {
        if ($this->is_32bit() === true ) {
            list($higher, $lower) = array_values(unpack("L2", $raw)); 
            return $higher << 32 | $lower;
        } else {
            return unpack("Q", $raw)[1];
        }
    }

    private function ipcmp($a, $b) {
        for ($i=0; $i<16; $i++) {
            if ($a[$i] > $b[$i])
                return 1;
            elseif ($a[$i] < $b[$i])
                return -1;
        }
        return 0;
    }
    
    private function ip_to_addr($ip) {
        $ret = array();
        $in_addr = inet_pton($ip);
        if ($in_addr !== false) {
            if (strlen($in_addr) == 4) {
                for ($i=0; $i<12; $i++)
                    array_push($ret, 0);
                for ($i=0; $i<4; $i++)
                    array_push($ret, unpack("C", $in_addr[$i])[1]);
            } else {
                for ($i=0; $i<16; $i++)
                    array_push($ret, unpack("C", $in_addr[$i])[1]);
            }
        } else {
            for ($i=0; $i<16; $i++)
                array_push($ret, 0);
        }
        return $ret;
    }

    public function find($ip) {
        $addr = $this->ip_to_addr($ip);
        $start = 0;
        $mid = 0;
        $end = ($this->index_size) - 1;
        while ($start <= $end) {
            $mid = intval(($start + $end) / 2);
            $mid_addr = array_values(unpack("C16", substr($this->index, $mid * 24, 16))); 
            $cmp = $this->ipcmp($mid_addr, $addr);
            if($cmp > 0) {
                $end = $mid;
            } elseif($cmp < 0) {
                $start = $mid;
                if($start == $end - 1) {
                    $_offset = substr($this->index, $mid * 24 + 16, 4);
                    $offset = unpack("L", $_offset)[1];
                    $_len = substr($this->index, $mid * 24 + 20, 4);
                    $len = unpack("L", $_len)[1];
                    return substr($this->data, $offset, $len);
                }
            } elseif($cmp == 0) {
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
