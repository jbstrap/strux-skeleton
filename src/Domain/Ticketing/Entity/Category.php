<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Entity;

use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Attributes\Unique;
use Strux\Component\Database\Types\Field;
use Strux\Component\Model\Attributes\BelongsToMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'categories')]
class Category extends Model
{
    #[Id, Column(type: Field::integer)]
    public ?int $categoryId = null;

    #[Column(type: Field::integer)]
    public ?int $parentId = null;

    #[Column]
    public ?string $title = null;

    #[Column]
    public ?string $description = null;

    #[Column]
    #[Unique]
    public ?string $slug = null;

    #[Column]
    public ?string $image = null;

    #[Column]
    public ?string $headerImage = null;

    #[Column]
    public bool $featured = false;

    #[BelongsToMany(Product::class, 'products_categories', 'categoryId', 'productId')]
    public Collection $products;

    public function __construct(array $attributes = [])
    {
        $this->products = new Collection();
        parent::__construct($attributes);
    }
}