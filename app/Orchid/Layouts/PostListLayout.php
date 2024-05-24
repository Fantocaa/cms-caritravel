<?php

namespace App\Orchid\Layouts;

use App\Models\Post;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PostListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'posts';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('title', 'Title')
                ->render(function (Post $post) {
                    return Link::make($post->title)
                        ->route('platform.post.edit', $post);
                }),

            TD::make('image', 'Image')
                ->render(function (Post $post) {
                    $imageUrl = $post->getImageUrlAttribute();

                    return $imageUrl ? "<img src='{$imageUrl}' alt='{$post->title}' style='width: 50px; height: 50px;'>" : 'No Image';
                })
                ->width('100px'), // Optional: Set a fixed width for the column

            // TD::make('created_at', 'Created'),
            // TD::make('updated_at', 'Last edit'),



        ];
    }
}
