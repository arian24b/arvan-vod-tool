<?php

namespace WP_Arvan\Engine;

class Helper
{
	public static function sanitize_recursive(&$input, $sanitizer){
		if (empty($input))
			return;
		if( !is_array($input) ) {

			$input = call_user_func($sanitizer, $input);

		}
		else{
			foreach ($input as $key => &$item){
				self::sanitize_recursive($item, $sanitizer);

			}
		}

	}

	function paging(&$query,$cnt=10){

        $count=$cnt;
        $page = 0;
        if(isset($_POST['paging']) or isset($_POST['__paging']))
        $page = isset($_POST['paging'])?array_keys($_POST['paging'])[0]:$_POST['__paging'];
        $max_page=intval(count($query)/$count);
        if((count($query)%$count)==0)
        --$max_page;
        $query = array_slice($query,$count*$page,$count);
        ob_start();
?>
<div class="arvvod-pagination">
	<style>
		.pagination button{
			background-color:transparent;
			border:none;
			cursor: pointer;
		}
	</style>
    <ul class="pagination justify-content-center mb-0">
        <li class="page-item">
			
		</li>
        <li class="page-item">
			
		</li>

		<div class="arvvod-pagination">
				<button type="submit" class="arvvod-pagination__next" name="paging[<?php echo '0'; ?>]" class="btn" value="First" <?php if($page==0)echo "disabled" ?>>
				<i class="arvicon chevron-right"></i>
				<i class="arvicon chevron-right"></i>
				</button>
				<button type="submit" class="arvvod-pagination__next" name="paging[<?php if($page==0)echo "0"; else echo $page-1; ?>]" class="btn" value="Previous" <?php if($page==0)echo "disabled" ?>>
				<i class="arvicon chevron-right"></i>
				</button>
					<div class="arvvod-pagination__list">
						<ul class="arvvod-pagination__list-ul">
						<?php
						for($i=0;$i<=$max_page;++$i){
							echo '
							<li class="arvvod-pagination__list-ul--li">
								<button type="submit" name="paging['.$i.']" class="arvvod-pagination__list-ul--a'.($i==$page?' active':'').'">'.$this->digits_enToFa($i+1).'</button>
							</li>';
						}
						?>
						</ul>
					</div>
				<button type="submit" class="arvvod-pagination__prev" name="paging[<?php if($page<$max_page)echo $page+1;else echo "$max_page"; ?>]" class="btn" value="Next" <?php if($page==$max_page)echo "disabled" ?>>
				<i class="arvicon chevron-left"></i>
				</button>
				<button type="submit" class="arvvod-pagination__prev" name="paging[<?php echo $max_page; ?>]" class="btn" value="Last" <?php if($page==$max_page)echo "disabled" ?>>
				<i class="arvicon chevron-left"></i>
				<i class="arvicon chevron-left"></i>
				</button>
				<input type="hidden" name="__paging" value="<?php echo $page ?>" />	
		</div>
<?php
        return ob_get_clean();
    }

	function convert_minsec($seconds){
        return $this->digits_enToFa(sprintf('%02d:%02d', ($seconds/ 60 % 60), $seconds% 60));
    }

	public static function digits_enToFa($string) {
		if(is_rtl())
			return strtr($string, array('0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹'));
		else
			return $string;
    }

	function file_size_format($size) {
		if ($size >= 1073741824) { // 1 GB
			return self::digits_enToFa(round($size / 1073741824, 2)) . ' GB';
		} elseif ($size >= 1048576) { // 1 MB
			return self::digits_enToFa(round($size / 1048576, 2)) . ' MB';
		} elseif ($size >= 1024) { // 1 KB
			return self::digits_enToFa(round($size / 1024, 2)) . ' KB';
		} else {
			return self::digits_enToFa($size) . ' bytes';
		}
	}
}
