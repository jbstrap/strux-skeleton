<?php

declare(strict_types=1);

namespace App\Domain\Ticketing\Entity;

use Strux\Component\Database\Attributes\Column;
use Strux\Component\Database\Attributes\Id;
use Strux\Component\Database\Attributes\Table;
use Strux\Component\Database\Types\Field;
use Strux\Component\Model\Attributes\BelongsToMany;
use Strux\Component\Model\Model;
use Strux\Support\Collection;

#[Table(name: 'products')]
class Product extends Model
{
    #[Id, Column(type: Field::integer)]
    public ?int $productId = null;

    #[Column]
    public ?string $title = null;

    #[BelongsToMany(
        Category::class, 'products_categories', 'productId', 'categoryId'
    )]
    public Collection $categories;

    public function __construct(array $attributes = [])
    {
        $this->categories = new Collection();
        parent::__construct($attributes);
    }
}