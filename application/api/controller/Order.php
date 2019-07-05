<?php
namespace app\api\controller;
use think\Db;
use think\Request;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\OrderLogic;
use app\common\model\Order as OrderModel;

class Order extends Base
{
	public function _initialize()
	{
		parent::_initialize();
    }
    
    public function getLogic()
    {
        return new OrderLogic();
    }
    
	//订单列表
    public function index()
	{
        //参数
        $limit = input('limit', 10);
        $offset = input('offset', 0);
        
		$where['user_id'] = $this->login_info['id'];
		//0或者不传表示全部，1待付款，2待发货,3待收货,4待评价(确认收货，交易成功),5退款/售后
        $status = input('status', '');
		if($status !== '' && $status != 0)
		{
			if($status == 1)
			{
				$where['order_status'] = 0;
				$where['pay_status'] = 0;
			}
			elseif($status == 2)
			{
				$where['order_status'] = 0;
				$where['shipping_status'] = 0;
				$where['pay_status'] = 1;
			}
			elseif($status == 3)
			{
				$where['order_status'] = 0;
				$where['shipping_status'] = 1;
				$where['pay_status'] = 1;
				$where['refund_status'] = 0;
			}
			elseif($status == 4)
			{
				$where['order_status'] = 3;
				$where['shipping_status'] = 2;
				$where['is_comment'] = 0;
				$where['refund_status'] = 0;
			}
			elseif($status == 5)
			{
				$where['order_status'] = 3;
				$where['refund_status'] = array('<>',0);
			}
		}
		
        $res = $this->getLogic()->getList($where, 'id desc', '*', $offset, $limit);
		
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
	//订单详情
    public function detail()
	{
        //参数
        if(!checkIsNumber(input('id',null))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->getOne($where);
		if(!$res){exit(json_encode(ReturnData::create(ReturnData::RECORD_NOT_EXIST)));}
        
		exit(json_encode(ReturnData::create(ReturnData::SUCCESS, $res)));
    }
    
    //添加
    public function add()
    {
        $data['default_address_id'] = input('default_address_id','');
        $data['user_bonus_id'] = input('user_bonus_id','');
        $data['shipping_costs'] = input('shipping_costs','');
        $data['message'] = input('message','');
        $data['place_type'] = input('place_type',2); //订单来源：1pc，2weixin，3app，4wap，5miniprogram
        
        //获取商品列表
        $data['cartids'] = input('cartids','');
        if($data['cartids']==''){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        
        if(Helper::isPostRequest())
        {
            $data['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->add($data);
			exit(json_encode($res));
        }
    }
    
    //修改
    public function edit()
    {
        if(!checkIsNumber(input('id',''))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            unset($_POST['id']);
            $where['id'] = $id;
            $where['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->edit($_POST,$where);
			exit(json_encode($res));
        }
    }
    
    //删除
    public function del()
    {
        if(!checkIsNumber(input('id',''))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        if(Helper::isPostRequest())
        {
            $where['id'] = $id;
            $where['user_id'] = $this->login_info['id'];
            
            $res = $this->getLogic()->del($where);
			exit(json_encode($res));
        }
    }
    
    //用户-取消订单
    public function user_cancel_order()
	{
        if(!checkIsNumber(input('id',''))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->userCancelOrder($where);
		exit(json_encode($res));
    }
    
    //订单-余额支付
    public function order_yuepay()
	{
        if(!checkIsNumber(input('id',''))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderYuepay($where);
		exit(json_encode($res));
    }
    
    //用户-确认收货
    public function user_receipt_confirm()
	{
        if(!checkIsNumber(input('id',''))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderReceiptConfirm($where);
		exit(json_encode($res));
    }
    
    //用户-退款退货
    public function user_order_refund()
	{
        if(!checkIsNumber(input('id',null))){exit(json_encode(ReturnData::create(ReturnData::PARAMS_ERROR)));}
        $id = input('id');
        
        $where['id'] = $id;
        $where['user_id'] = $this->login_info['id'];
        
        $res = $this->getLogic()->orderRefund($where);
		exit(json_encode($res));
    }
    
    //商城支付宝APP支付
	public function order_alipay_app()
    {
        $id = input('id',null);
        if($id===null){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order = DB::table('order')->where(['id'=>$id,'status'=>0,'user_id'=>Token::$uid])->first();
        if(!$order){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_pay = DB::table('order_pay')->where(['id'=>$order->pay_id])->first();
        if(!$order_pay){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        require_once base_path('resources/org/alipay_app').'/AopClient.php';
        require_once base_path('resources/org/alipay_app').'/AlipayTradeAppPayRequest.php';
        
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = config('alipay.app_alipay.appId');
        $aop->rsaPrivateKey = config('alipay.app_alipay.rsaPrivateKey');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = config('alipay.app_alipay.alipayrsaPublicKey');
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"订单支付\"," 
                        . "\"subject\": \"订单支付\","
                        . "\"out_trade_no\": \"".$order_pay->sn."\","
                        . "\"total_amount\": \"".$order_pay->pay_amount."\","
                        . "\"timeout_express\": \"30m\"," 
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $request->setNotifyUrl(config('app.url.apiDomain') . '/payment/notify/order_alipay/');
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        return ReturnCode::create(ReturnCode::SUCCESS,$response);//就是orderString 可以直接给客户端请求，无需再做处理。
    }
    
    //商城微信APP支付
	public function order_wxpay_app()
    {
        //参数
		$id = input('id',null);
        if($id===null){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_info = DB::table('order')->where(['id'=>$id,'status'=>0,'user_id'=>Token::$uid])->first();
        if(!$order_info){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
        $order_pay = DB::table('order_pay')->where(['id'=>$order_info->pay_id])->first();
        if(!$order_pay){return ReturnCode::create(ReturnCode::PARAMS_ERROR);}
        
		//1.配置
		$options = config('weixin.app');
        
		$app = new \EasyWeChat\Foundation\Application($options);
		$payment = $app->payment;
		$out_trade_no = $order_pay->sn;
        
		//2.创建订单
		$attributes = [
			'trade_type'       => 'APP', // JSAPI，NATIVE，APP...
			'body'             => '订单支付',
			'detail'           => '订单支付',
			'out_trade_no'     => $out_trade_no,
			'total_fee'        => $order_pay->pay_amount*100, // 单位：分
			'notify_url'       => config('app.url.apiDomain').'payment/notify/app_order_weixin_pay/', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
			//'openid'           => '当前用户的 openid', // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
			// ...
		];
        
		$order = new \EasyWeChat\Payment\Order($attributes);
        
		//3.统一下单
		$result = $payment->prepare($order);
        
		if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS')
		{
			$prepayId = $result->prepay_id;
			$res = $payment->configForAppPayment($prepayId);
		}
        
		$res['out_trade_no'] = $out_trade_no;

		return ReturnCode::create(ReturnCode::SUCCESS,$res);
    }
}