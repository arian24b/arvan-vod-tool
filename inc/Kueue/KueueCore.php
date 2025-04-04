<?php

namespace WP_Arvan\Engine\Kueue;

class KueueCore
{

    private static $instance;
    private $jobs;

    public function __construct(){

        $this->jobs =   array();
        add_filter( 'action_scheduler_retention_period', array($this, 'kueue_change_completed_task_deletion_period' ));
    }
    public static function get_instance(){

        if( null == self::$instance )
            self::$instance = new KueueCore();
        return self::$instance;

    }

    public function add_job($timestamp=0,$interval=0,$hook='', $arg=array(), $group=null){

        $job                = array();
        $job['timestamp']   = $timestamp;
        $job['interval']    = $interval;
        $job['hook']        = $hook;
        $job['arg']         = $arg;
        $job['group']       = $group;

        $this->jobs[] =   $job;

    }

    public function schedule_jobs(){

        foreach($this->jobs as $job){
            if(empty($job['timestamp']) && empty($job['interval'])){
                $this->schedule_immediate_job($job);
            }else if( empty($job['interval']) ){
                $this->schedule_single_time_job($job);
            }else{
                $this->schedule_repeating_job($job);
            }
        }

    }

    private function schedule_immediate_job($job){
        as_enqueue_async_action( $job['hook'], $job['arg'],$job['group'] );
    }

    private function schedule_single_time_job($job){
        as_schedule_single_action( $job['timestamp'], $job['hook'], $job['arg'],$job['group'] );
    }

    private function schedule_repeating_job($job){
        as_schedule_recurring_action( $job['timestamp'],$job['interval'], $job['hook'], $job['arg'],$job['group'] );
    }

    public function has_pending_job($hook){

            return as_has_scheduled_action( $hook );
    }


    public function stop_process($hook){
        as_unschedule_all_actions($hook);
        return true;
    }

    /**
     * Change Action Scheduler default purge to 1 day
     */
    function kueue_change_completed_task_deletion_period() {
        return DAY_IN_SECONDS;
    }

}
