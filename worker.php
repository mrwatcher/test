<?php
/** 
*pre-fork 多进程模型
* 
* @author     chens
* @version     1.1
* todo: 添加kill 信号处理
*/ 
class worker
{

	private static $pids = array();

	private static $workers = array();

	//某进程执行某内容关系映射
	private static $p2w =  array();

	//拉起次数计数
	private static $pull_num =  array();
	/**
	 * 抽象注入多个依赖子进程
	 */
	public static function run()
	{
		$workers = func_get_args();
		if (!empty($workers)) {
			foreach ($workers as $worker) {
				if (is_callable($worker)) {
					self::$workers[] = $worker;
				}
			}
		}
		//执行pre-fork模型
		self::_fork();
	}

	//pre-fork 模型
	private static function _fork()
	{
		$c_func = self::$workers;
		if (!empty($c_func)) {
			$fork_num = count($c_func);
			$pids = array();
			for ($i=0; $i < $fork_num; $i++) {
				$child = $c_func[$i];
				self::$pull_num[$i] = 0;
				$pid = pcntl_fork();
				if ($pid == -1) {die('FORK_ERROR');}else if($pid){
					self::$pids[$pid] = $child;
					self::$p2w[$pid] = $i;
					//最后fork父进程阻塞,监控所有子进程,必要时重新拉起
					if( $i == ($fork_num-1) ){
						self::father_work();
					};
				}else{
					$child(self::$pull_num[$i]);die;//die防止子进程继续衍生孙子进程
				}
			}
		}else{
			die('WORKERS_EMPTY');
		}
	}

	//父进程作为监控进程,监控其子进程健康状况
	private static function father_work()
	{
		while(($pid = pcntl_waitpid(-1, $status, WUNTRACED)) != 0){
    		// 退出的子进程pid  
    		if($pid>0){
        		//重新拉起子进程
        		$newchild = self::$pids[$pid];
        		//获取退出进程执行的是那个work
				$worker_id = self::$p2w[$pid];
        		self::$pull_num[$worker_id] = self::$pull_num[$worker_id] + 1;
        		//手动退出子进程
        		if($status == 35) {
        			unset(self::$pids[$pid]);
        			unset(self::$p2w[$pid]);
        		}elseif($status == 34){
        			$newpid = pcntl_fork();
        			self::$pull_num[$worker_id] = 0;
					if ($newpid == -1) {die('FORK_ERROR');}else if($newpid){
						self::$pids[$newpid] = $newchild;
						//写入新子进程对应关系
						self::$p2w[$newpid] = $worker_id;
					}else{
						$newchild(self::$pull_num[$worker_id]);die;
					}
        		}else{
        			$newpid = pcntl_fork();
					if ($newpid == -1) {die('FORK_ERROR');}else if($newpid){
						self::$pids[$newpid] = $newchild;
						//写入新子进程对应关系
						self::$p2w[$newpid] = $worker_id;
					}else{
						$newchild(self::$pull_num[$worker_id]);die;
					}
        		}
    		}else{
        		die('WORKER_CHILD_ALL_DIE');
    		}
		}

	}

}
/**
*调用demo
*注意:进程之间相互隔离,内存空间不共享,变量不共享,进程间通信借助kqueue等...
* worker::run(fucntion(){/子进程函数/},function(){/子进程函数/},function(){/子进程函数/});
*/
/*
worker::run(
function(){
		for ($i=0; $i <50000; $i++) { 
				echo '111'.PHP_EOL;
				sleep(3);
		}
		exit();
},
function(){
		for ($i=0; $i <50000; $i++) { 
				echo '222'.PHP_EOL;
				sleep(5);
		}
		exit();
},
function(){
		for ($i=0; $i <50000; $i++) { 
				echo '333'.PHP_EOL;
				sleep(7);
		}
		exit();
});
*/
?>