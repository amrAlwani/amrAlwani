<?php
/**
 * API ReviewController
 */

namespace Api;

require_once BASEPATH . '/models/Review.php';

class ReviewController extends \Controller
{
    private \Review $reviewModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new \Review();
    }
    
    /**
     * تقييمات منتج
     */
    public function byProduct(string $id): void
    {
        $reviews = $this->reviewModel->getByProduct((int)$id);
        \Response::success($reviews);
    }
    
    /**
     * إضافة تقييم
     */
    public function store(): void
    {
        $user = \Auth::requireAuth();
        $data = $this->getJsonInput();
        
        $validator = new \Validator($data);
        $validator->required('product_id', 'معرف المنتج مطلوب')
                  ->required('rating', 'التقييم مطلوب')
                  ->integer('rating', 'التقييم غير صالح');
        
        if (!$validator->passes()) {
            \Response::validationError($validator->getErrors());
        }
        
        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            \Response::error('التقييم يجب أن يكون بين 1 و 5', [], 400);
        }
        
        // التحقق من وجود تقييم سابق
        if ($this->reviewModel->exists($user['id'], (int)$data['product_id'])) {
            \Response::error('لقد قمت بتقييم هذا المنتج مسبقاً', [], 400);
        }
        
        $reviewId = $this->reviewModel->create([
            'user_id' => $user['id'],
            'product_id' => (int)$data['product_id'],
            'order_id' => $data['order_id'] ?? null,
            'rating' => $rating,
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_approved' => 0
        ]);
        
        $review = $this->reviewModel->findById($reviewId);
        
        \Response::created($review, 'تم إضافة التقييم وسيتم مراجعته');
    }
}
