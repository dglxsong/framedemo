<?php

class BravePaginate extends Brave {
    
    var $count = 7;
    var $addition = 1;

    function parse($paging) {
        // count
        $result = array(
            'pres' => 0,
            'pre' => 0,
            'next' => 0,
            'nexts' => 0,
            'start' => 0,
            'main' => null,
            'end' => 0,
        );

        // pages
        $pages = $paging['pages'];
        
        // pres
        if ($paging['page'] <= $this->count) {
            $pres = 0;
        }
        else {
            $pres = $paging['page'] - $this->count;
        }

        // pre
        if ($paging['page'] == 1) {
            $pre = 0;
            $pres = 0;
        }
        else {
            $pre = $paging['page'] - 1;
            $pres = 1;
        }

        // next
        if ($paging['page'] == $pages) {
            $next = 0;
            $nexts = 0;
        }
        else {
            $next = $paging['page'] + 1;
            $nexts = $paging['page'] + $this->count;
        }
        
        // nexts
        /*
        if ($paging['page'] > $pages - $this->count) {
            $nexts = 0;
        }
        else {
            $nexts = $paging['page'] + $this->count;
        }
        */
        
        // paginate
        $max = $this->count + $this->addition;
        $offset = intval($this->count / 2);
        
        // 123456789
        if ($pages <= $max) {
            $start = null;
            $main = array(
                'start' => 1,
                'count' => $pages,
            );
            $end = null;
        }
        // 1234567...100
        else if ($paging['page'] < ($this->count)) {
            $start = 0;
            $main = array(
                'start' => 1, 
                'count' => $this->count,
            );
            $end = $pages;
        }
        // 1...93949596979899100
        else if ($paging['page'] > ($pages - $this->count)) {
            $start = 1;
            $main = array(
                'start' => $pages - $this->count + 1, 
                'count' => $this->count,
            );
            $end = 0;
        }
        // 1...3456789...100
        // 1...92939495969798...100
        else {
            $start = 1;
            $main = array(
                'start' => $paging['page'] - $offset,
                'count' => $this->count,
            );
            $end = $pages;
        }
        
        $result = array(
            'pres' => $pres,
            'pre' => $pre,
            'next' => $next,
            'nexts' => $nexts,
            'start' => $start,
            'main' => $main,
            'end' => $end,
        );

        return $result;
    }
}

?>
