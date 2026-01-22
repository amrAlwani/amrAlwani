<?php
/**
 * API AddressController
 */

namespace Api;

require_once BASEPATH . '/models/Address.php';

class AddressController extends \Controller
{
    private \Address $addressModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->addressModel = new \Address();
    }
    
    /**
     * قائمة العناوين
     */
    public function index(): void
    {
        $user = \Auth::requireAuth();
        $addresses = $this->addressModel->getByUser($user['id']);
        
        \Response::success($addresses);
    }
    
    /**
     * إضافة عنوان
     */
    public function store(): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->required('phone', 'رقم الهاتف مطلوب')
                  ->required('city', 'المدينة مطلوبة')
                  ->required('street', 'العنوان مطلوب');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $isFirst = empty($this->addressModel->getByUser($user['id']));
        
        $addressId = $this->addressModel->create([
            'user_id' => $user['id'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'district' => $data['district'] ?? '',
            'street' => $data['street'],
            'building' => $data['building'] ?? '',
            'floor' => $data['floor'] ?? '',
            'apartment' => $data['apartment'] ?? '',
            'notes' => $data['notes'] ?? '',
            'is_default' => $isFirst || !empty($data['is_default']) ? 1 : 0
        ]);
        
        $address = $this->addressModel->findById($addressId);
        
        \Response::created($address, 'تم إضافة العنوان');
    }
    
    /**
     * تحديث عنوان
     */
    public function update(string $id): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        $address = $this->addressModel->findById((int)$id);
        
        if (!$address || $address['user_id'] != $user['id']) {
            \Response::notFound('العنوان غير موجود');
        }
        
        $this->addressModel->update((int)$id, [
            'name' => $data['name'] ?? $address['name'],
            'phone' => $data['phone'] ?? $address['phone'],
            'city' => $data['city'] ?? $address['city'],
            'district' => $data['district'] ?? $address['district'],
            'street' => $data['street'] ?? $address['street'],
            'building' => $data['building'] ?? $address['building'],
            'floor' => $data['floor'] ?? $address['floor'],
            'apartment' => $data['apartment'] ?? $address['apartment'],
            'notes' => $data['notes'] ?? $address['notes'],
            'is_default' => isset($data['is_default']) ? ($data['is_default'] ? 1 : 0) : $address['is_default']
        ]);
        
        $updatedAddress = $this->addressModel->findById((int)$id);
        
        \Response::success($updatedAddress, 'تم تحديث العنوان');
    }
    
    /**
     * حذف عنوان
     */
    public function delete(string $id): void
    {
        $user = \Auth::requireAuth();
        $address = $this->addressModel->findById((int)$id);
        
        if (!$address || $address['user_id'] != $user['id']) {
            \Response::notFound('العنوان غير موجود');
        }
        
        $this->addressModel->delete((int)$id);
        
        \Response::success(null, 'تم حذف العنوان');
    }
}
