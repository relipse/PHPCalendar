<?php
/**
 * Calendar Class
 * This Calendar class was started from the Calendar
 * code on CodeShack (https://codeshack.io/event-calendar-php/ accessed: 2022-11-18),
 * It has been heavily modified by Jim Kinsman.
 * @contrib Jim Kinsman
 */
class Calendar {
    protected $timezone;
    protected $active_year, $active_month, $active_day;
    protected $events = [];

    protected $show_time_in_short_title = true;
    protected $show_nonactive_month_name = false;
    protected $show_active_month_name_on_first = false;
    protected $num_weeks_visible = 5;
    protected $hide_previous_month = false;
    protected $hide_next_month = false;

    public function setShowTimeInShortTitle($b){
        $this->show_time_in_short_title = (bool)$b;
    }

    public function setShowNonActiveMonthName($b){
        $this->show_nonactive_month_name = (bool)$b;
    }

    public function setShowActiveMonthNameOnFirst($b){
        $this->show_active_month_name_on_first = (bool)$b;
    }

    public function setHidePreviousMonth($b){
        $this->hide_previous_month = (bool)$b;
    }

    public function setHideNextMonth($b){
        $this->hide_next_month = (bool)$b;
    }

    public function setNumWeeksVisible($weeks){
        if ($weeks < 1){
            throw new \Exception("Invalid. Weeks must be greater than 0. You set: $weeks");
        }
        $this->num_weeks_visible = (int)$weeks;
    }

    public function sort(){
        $date = array_column($this->events, 'date');
        array_multisort($date, SORT_ASC, $this->events);
    }

    public function __construct(array $opts = []) {
        if (isset($opts['timezone'])){
            $this->timezone = $opts['timezone'];
        }else{
            $this->timezone = 'America/New_York';
        }
        if (isset($opts['active_date'])){
            $active_date = $opts['active_date'];
        }
        if (empty($active_date)){
            $active_date = 'now';
        }
        $dtactive = new \DateTime($active_date, new \DateTimeZone($this->timezone));

        $this->active_year = $dtactive->format('Y');
        $this->active_month = $dtactive->format('m');
        $this->active_day = $dtactive->format('d');

        if (isset($opts['show_time_in_short_title'])){
            $this->show_time_in_short_title = (bool)$opts['show_time_in_short_title'];
        }
        if (!empty($opts['show_nonactive_month_name'])){
            $this->show_nonactive_month_name = true;
        }
        if (!empty($opts['show_active_month_name_on_first'])){
            $this->show_active_month_name_on_first = true;
        }
        if (!empty($opts['num_weeks_visible'])){
            $this->num_weeks_visible = $opts['num_weeks_visible'];
            if ($this->num_weeks_visible < 1){
                throw new \Exception($this->num_weeks_visible.' is an invalid amount of weeks.');
            }
        }
        if (!empty($opts['hide_previous_month'])){
            $this->hide_previous_month = true;
        }
        if (!empty($opts['hide_next_month'])){
            $this->hide_next_month = true;
        }
    }

    public function add_event_details_ary($ary){
        if (!isset($ary['short_title'])){
            throw new \Exception("Must have short_title");
        }
        if (!isset($ary['link'])){
            $ary['link'] = '';
        }
        if (!isset($ary['hover_title'])){
            $ary['hover_title'] = '';
        }
        if (!isset($ary['date'])){
            throw new \Exception("Must have date");
        }
        if (!isset($ary['days_span'])){
            $ary['days_span'] = 1;
        }
        if (!isset($ary['color'])){
            $ary['color'] = '';
        }
        $this->add_event_details($ary['short_title'], $ary['link'], $ary['hover_title'], $ary['date'], $ary['days_span'], $ary['color']);
    }

    public function add_event_details($short_title, $link, $hover_title, $datetime, $days_span = 1, $color = ''){
        $color = $color ? ' ' . $color : $color;
        $this->events[] = ['short_title'=>$short_title, 'hover_title'=> $hover_title, 'link'=>$link, 'date'=>$datetime, 'days_span'=>$days_span, 'color'=>$color];
    }

    public function add_event($txt, $date, $days = 1, $color = '') {
        $color = $color ? ' ' . $color : $color;
        $this->events[] = ['short_title'=>$txt, 'date'=>$date, 'days_span'=>$days, 'color'=>$color, 'link'=>'', 'hover_title'=>''];
    }

    public function iterateEvents($i, $year = null, $month = null, &$num_events = null){
        if (is_null($year)){
            $year = $this->active_year;
        }
        if (is_null($month)){
            $month = $this->active_month;
        }
        $num_events = 0;
        $html = '';
        foreach ($this->events as $event) {
            for ($d = 0; $d <= ($event['days_span']-1); $d++) {
                if (date('y-m-d', strtotime($year . '-' . $month . '-' . $i . ' -' . $d . ' day')) == date('y-m-d', strtotime($event['date']))) {
                    $num_events++;
                    $html .= '<div class="event' . $event['color'].'"';
                    if (!empty($event['hover_title'])){
                        $html .= ' title="'.htmlentities($event['hover_title']).'"';
                    }
                    $html .= '>';
                    if (!empty($event['link'])){
                        $html .= '<a href="'.htmlentities($event['link']).'">';
                    }
                    if ($this->show_time_in_short_title && strpos($event['date'], ':') !== false){
                        $dtevt = new \DateTime($event['date'], new \DateTimeZone($this->timezone));
                        $short = $dtevt->format('gia');
                        $short = str_replace('00','',$short);
                        $short = str_replace('m','',$short);
                        $html .= $short.' ';
                    }
                    $html .= $event['short_title'];
                    if (!empty($event['link'])){
                        $html .= '</a>';
                    }
                    $html .= '</div>';
                }
            }
        }
        if ($return_num_events_instead){
            return $num_events;
        }
        return $html;
    }

    public function __toString() {
        $num_days = date('t', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year));
        $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year)));
        $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $first_day_of_week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);
        $html = '<div class="calendar">';
        $html .= '<div class="header">';
        $html .= '<div class="month-year">';
        $html .= date('F Y', strtotime($this->active_year . '-' . $this->active_month . '-' . $this->active_day));
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="days">';
        foreach ($days as $day) {
            $html .= '
                <div class="day_name">
                    ' . $day . '
                </div>
            ';
        }
        $dt = new \DateTime($this->active_year.'-'.$this->active_month.'-01 00:00:00');
        $dtlastmonth = clone $dt;
        $dtlastmonth->modify('-1 month');
        //if (!$this->hide_previous_month) {
            for ($i = $first_day_of_week; $i > 0; $i--) {
                $iterated_events_html = $this->iterateEvents($num_days_last_month - $i + 1, $dtlastmonth->format('Y'), $dtlastmonth->format('m'), $num_events);
                $html .= '
                <div class="day_num ignore'.($this->hide_previous_month ? ' hidemonth':'').($i === 1?' last':'').
                    '" title="'.$num_events.' event'.($num_events !== 1?'s':'').'">
                    ';

                if (!$this->hide_previous_month) {
                    if ($this->show_nonactive_month_name && $i === $first_day_of_week) {
                        $html .= '<span class="nonactivemonth">' . $dtlastmonth->format('M') . '</span>';
                    }
                    $html .= '<span>' . ($num_days_last_month - $i + 1) . '</span>';
                    $html .= $iterated_events_html;
                }
                $html .= '
                </div>
            ';
            }
        //}
        for ($i = 1; $i <= $num_days; $i++) {
            $iterated_events_html = $this->iterateEvents($i, null, null, $num_events);

            $selected = '';
            if ($i == $this->active_day) {
                $selected = ' selected';
            }
            $html .= '<div class="day_num' . $selected . '" title="'.$num_events.' event'.($num_events !== 1?'s':'').'">';
            if ($this->show_active_month_name_on_first && $i === 1){
                $html .= '<span class="activemonthname">'.$dt->format('M').'</span>';
            }
            $html .= '<span>' . $i . '</span>';
            $html .= $iterated_events_html;
            $html .= '</div>';
        }

        $dt->modify('+1 month');

        $numdaysvisible = 7*$this->num_weeks_visible;
        if (!$this->hide_next_month) {
            for ($i = 1; $i <= ($numdaysvisible - $num_days - max($first_day_of_week, 0)); $i++) {
                $iterated_events_html = $this->iterateEvents($i, $dt->format('Y'), $dt->format('m'), $num_events);
                $html .= '
                <div class="day_num ignore" title="'.$num_events.' event'.($num_events !== 1?'s':'').'">
                    ';
                if ($this->show_nonactive_month_name && $i === 1) {
                    $html .= '<span class="nonactivemonth">' . $dt->format('M') . '</span>';
                }

                $html .= '<span>';
                $html .= $i . '</span>';
                $html .= $iterated_events_html;

                $html .= '
                </div>
            ';
            }
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }
}
