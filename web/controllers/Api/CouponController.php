<?php
/**
 * API CouponController
 */

namespace Api;

require_once BASEPATH . '/models/Coupon.php';

class CouponController extends \Controller
{
    private \Coupon $couponModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->couponModel = new \Coupon();
    }
    
    /**
     * التحقق من صلاحية الكوبون
     */
    public function validate(): void
    {
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('code', 'كود الكوبون مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $coupon = $this->couponModel->findByCode($data['code']);
        
        if (!$coupon) {
            \Response::error('الكوبون غير موجود', [], 404);
        }
        
        // التحقق من الصلاحية
        $validation = $this->couponModel->validate($data['code'], $data['subtotal'] ?? 0);
        
        if (!$validation['valid']) {
            \Response::error($validation['message'], [], 400);
        }
        
        \Response::success([
            'coupon' => $coupon,
            'discount' => $validation['discount']
        ], 'الكوبون صالح');
    }
}
