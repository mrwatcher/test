<?php
/**
 * 经典trie树
 * by chens v1.0
 * 先放这 等大家想用了再说
 */
class trie
{
    public static $tree = array();
    public static function build($string)
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        //print_r($chars);die;
        $tree = &self::$tree;
        foreach ($chars as $char) {
            if (!isset($tree[$char])) {
                $tree[$char] = array();
            }
            $tree = &$tree[$char];
        }
        $tree['||||||CHENS_END||||||'] = '';
    }
    static public function find($string,$map = 1)
    {
        $flag = 0;
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        $tree = &self::$tree;
        $i = -1;
        $j = 0;
        $p;
        $str;
        foreach ($chars as $char) {
            $i++;
            if(isset($tree[$char])){
                $str[$j][] = $char;
                $tree = &$tree[$char];
                if (isset($tree['||||||CHENS_END||||||'])) {
                    $p[$j][] = $i;$flag = 1;$j++;
                    $tree = &self::$tree;
                    if ($map === 1) {
                        continue;
                    }else{
                        break;
                    }
                }
            }else{
                unset($str[$j]);
                $tree = &self::$tree;
                if(isset($tree[$char])){
                    $str[$j][] = $char;
                    $tree = &$tree[$char];
                    if (isset($tree['||||||CHENS_END||||||'])) {
                        $p[$j][] = $i;$flag = 1;$j++;
                        $tree = &self::$tree;
                        if ($map === 1) {
                            continue;
                        }else{
                            break;
                        }
                    }
                }
            }
        }
        if(count($p) != count($str)){
            unset($str[(count($str)-1)]);
        }
        return array('flag'=>$flag,'point'=>$p,'string'=>$str);
    }
}
$arr = include('./test/amc-news.php');
foreach ($arr as $key => $value) {
    trie::build($value);
}
//trie::$tree = unserialize(file_get_contents('./test/dealer_news_trie_tree.ser'));
//$res = trie::find("买车达人aabbcc易车",1);
print_r(serialize(trie::$tree));
//print_r(count(trie::$tree));