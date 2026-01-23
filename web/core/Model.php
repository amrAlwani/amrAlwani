<?php
/**
 * Model - الفئة الأساسية للـ Models
 */

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = ['password'];
    
    public function __construct()
    {
        $this->db = db();
    }
    
    /**
     * البحث بالمعرف
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hideFields($result) : null;
    }
    
    /**
     * الحصول على الكل
     */
    public function all(array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return array_map([$this, 'hideFields'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    /**
     * إنشاء سجل جديد
     */
    public function create(array $data): ?int
    {
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            return null;
        }
        
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * تحديث سجل
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        if (empty($data)) {
            return false;
        }
        
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }
    
    /**
     * حذف سجل
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * عد السجلات
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * البحث بشرط
     */
    public function findWhere(array $conditions, string $orderBy = 'id DESC'): array
    {
        return $this->all($conditions, $orderBy);
    }
    
    /**
     * البحث عن أول نتيجة
     */
    public function findFirst(array $conditions): ?array
    {
        $results = $this->findWhere($conditions);
        return $results[0] ?? null;
    }
    
    /**
     * تصفية الحقول المسموحة
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * إخفاء الحقول الحساسة
     */
    protected function hideFields(array $data): array
    {
        foreach ($this->hidden as $field) {
            unset($data[$field]);
        }
        return $data;
    }
    
    /**
     * تقسيم النتائج لصفحات
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = [], string $orderBy = 'id DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => array_map([$this, 'hideFields'], $stmt->fetchAll(PDO::FETCH_ASSOC)),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage)
        ];
    }
}
