<?php
/**
 * AddressController - العناوين
 */

require_once BASEPATH . '/models/Address.php';

class AddressController extends Controller
{
    private Address $addressModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->addressModel = new Address();
    }
    
    /**
     * قائمة العناوين
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $addresses = $this->addressModel->getByUser($user['id']);
        
        $this->view('addresses/index', [
            'title' => 'عناويني',
            'addresses' => $addresses
        ]);
    }
    
    /**
     * إضافة عنوان جديد
     */
    public function store(): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        if (!CSRF::validate($data['_csrf_token'] ?? '')) {
            $this->flash('error', 'انتهت صلاحية النموذج');
            $this->redirect('addresses');
        }
        
        $validator = new Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->required('phone', 'رقم الهاتف مطلوب')
                  ->required('city', 'المدينة مطلوبة')
                  ->required('street', 'العنوان مطلوب');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('addresses');
        }
        
        $isFirst = empty($this->addressModel->getByUser($user['id']));
        
        $this->addressModel->create([
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
        
        $this->flash('success', 'تم إضافة العنوان');
        $this->redirect('addresses');
    }
    
    /**
     * تحديث عنوان
     */
    public function update(string $id): void
    {
        $user = $this->requireAuth();
        $data = $this->getPostData();
        
        $address = $this->addressModel->findById((int)$id);
        
        if (!$address || $address['user_id'] != $user['id']) {
            $this->flash('error', 'العنوان غير موجود');
            $this->redirect('addresses');
        }
        
        $validator = new Validator($data);
        $validator->required('name', 'الاسم مطلوب')
                  ->required('phone', 'رقم الهاتف مطلوب')
                  ->required('city', 'المدينة مطلوبة')
                  ->required('street', 'العنوان مطلوب');
        
        if (!$validator->passes()) {
            $this->flash('error', implode('<br>', $validator->getErrors()));
            $this->redirect('addresses');
        }
        
        $this->addressModel->update((int)$id, $user['id'], [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'district' => $data['district'] ?? '',
            'street' => $data['street'],
            'building' => $data['building'] ?? '',
            'floor' => $data['floor'] ?? '',
            'apartment' => $data['apartment'] ?? '',
            'notes' => $data['notes'] ?? '',
            'is_default' => !empty($data['is_default']) ? 1 : 0
        ]);
        
        $this->flash('success', 'تم تحديث العنوان');
        $this->redirect('addresses');
    }
    
    /**
     * حذف عنوان
     */
    public function delete(string $id): void
    {
        $user = $this->requireAuth();
        $address = $this->addressModel->findById((int)$id);
        
        if (!$address || $address['user_id'] != $user['id']) {
            $this->flash('error', 'العنوان غير موجود');
            $this->redirect('addresses');
        }
        
        $this->addressModel->delete((int)$id, $user['id']);
        $this->flash('success', 'تم حذف العنوان');
        $this->redirect('addresses');
    }
}
