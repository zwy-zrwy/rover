<?php

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

/* 快递模块主类 */
class express
{
    /*构造函数*/
    function __construct()
    {
        $this->express();
    }

    /*构造函数*/
    function express()
    {
        

    }
	
	    /**
     * 计算订单的配送费用的函数
     *
     * @param   float   $goods_weight   商品重量
     * @param   float   $goods_amount   商品金额
     * @param   float   $goods_number   商品数量
     * @return  decimal
     */
    function calculate($shipping_config, $goods_weight, $goods_amount, $goods_number)
    {
        foreach ($shipping_config AS $key=>$val)
        {
            $configure[$val['name']] = $val['value'];
        }
        //print_r($configure);


        if ($configure['free_money'] > 0 && $goods_amount >= $configure['free_money'])
        {
            return 0;
        }
        else
        {

            @$fee = $configure['base_fee'];
            $configure['fee_compute_mode'] = !empty($configure['fee_compute_mode']) ? $configure['fee_compute_mode'] : 'by_weight';

            if ($configure['fee_compute_mode'] == 'by_number')
            {
                $fee = $goods_number * $configure['item_fee'];
            }
            else
            {
                if ($goods_weight > 1)
                {
                    $fee += (ceil(($goods_weight - 1))) * $configure['step_fee'];
                    //echo $configure['step_fee'];
                }
            }
           // $_SESSION['cart_weight'] = $goods_weight;
            return $fee;
        }
    }
}

?>