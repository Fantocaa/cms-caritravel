<?php

namespace App\Orchid\Screens;

use App\Models\Post;
use App\Orchid\Layouts\PostListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class PostListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'posts' => Post::paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        // return 'PostListScreen';
        return 'Travel Package';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function description(): ?string
    {
        return 'All Travel Package';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Create new')
                ->icon('pencil')
                ->route('platform.post.edit'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            PostListLayout::class,
        ];
    }
}
