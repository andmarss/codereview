<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 04.04.2019
 * Time: 11:00
 */

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

trait PaginateTrait
{
    /**
     * @param \Countable $items
     * @param int $perPage
     * @param int $page
     * @return \Countable
     */
    public function paginate(\Countable $items, int $perPage = 10, int $page = 1): \Countable
    {
        /**
         * @var int $page
         */
        $page = ((int) $page) - 1;
        $page = $perPage * max($page, 0);

        return $items->slice($page, $perPage)->values();
    }
    /**
     * Реффакторинг повторяющегося кода
     * @param \Countable $reviews
     * @param Request $request
     * @return array
     */
    public function paginateReviews(\Countable $reviews, Request $request): array
    {
        /**
         * @var int $total
         */
        $total = (int) $reviews->count();
        /**
         * @var Collection $reviews
         */
        $reviews = $this->paginate($reviews, (int) $request->per_page, (int) $request->page);

        foreach ($reviews as $review) {
            if($review->answered_at) {
                $review->date = (new \DateTime($review->answered_at))->format('d-m-Y H:i');
            }
        }

        return [$reviews, $total];
    }
}
