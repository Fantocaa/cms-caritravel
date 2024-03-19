<?php

namespace App\Orchid\Screens;

use App\Models\cities;
use App\Models\countries;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
// use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PostEditScreen extends Screen
{
    public $post;

    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(Post $post): iterable
    // public function query(Post $post): array
    {
        // Jika post ada, ubah start_date dan end_date menjadi array date
        if ($post->exists) {
            $post->date = [
                'start' => $post->start_date,
                'end' => $post->end_date,
            ];
        }

        return ['post' => $post];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        // return 'PostEditScreen';
        return $this->post->exists ? 'Edit post' : 'Creating a new post';
    }

    public function description(): ?string
    {
        return 'Blog posts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Create post')
                ->icon('pencil')
                ->method('createOrUpdate')
                ->canSee(! $this->post->exists),

            Button::make('Update')
                ->icon('note')
                ->method('createOrUpdate')
                ->canSee($this->post->exists),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->post->exists),
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
            Layout::rows([

                Relation::make('post.countries')
                    ->title('Negara')
                    ->placeholder('Enter Negara')
                    ->fromModel(countries::class, 'name'),

                Relation::make('post.cities')
                    ->title('Kota')
                    ->placeholder('Enter Kota')
                    ->fromModel(cities::class, 'name')
                    ->dependsOn('post.countries'),

                Input::make('post.traveler')
                    ->title('Traveler')
                    ->placeholder('Enter Traveler')
                    ->type('number'),

                DateRange::make('post.date')
                    ->title('Jadwal Perjalanan')
                    ->placeholder('Pilih Rentang Tanggal'),

                Input::make('post.duration')
                    ->title('Duration')
                    ->placeholder('Enter Duration'),

                Input::make('post.title')
                    ->title('Title')
                    ->placeholder('Attractive but mysterious title')
                    ->help('Specify a short descriptive title for this post.'),

                TextArea::make('post.description')
                    ->title('Description')
                    ->rows(3)
                    ->maxlength(200)
                    ->placeholder('Brief description for preview'),

                Relation::make('post.author')
                    ->title('Author')
                    ->fromModel(User::class, 'name'),

                Quill::make('post.body')
                    ->title('Main text'),

            ]),
        ];
    }

    public function createOrUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'post.countries' => 'required',
            'post.cities' => 'required',
            'post.traveler' => 'required|numeric',
            'post.date' => 'required|array',
            'post.date.start' => 'required|date',
            'post.date.end' => 'required|date|after_or_equal:post.date.start',
            'post.duration' => 'required',
            'post.title' => 'required|max:255',
            'post.description' => 'required|max:200',
            'post.author' => 'required',
            'post.body' => 'required',
        ], [
            'post.countries.required' => 'Negara tidak boleh kosong',
            'post.cities.required' => 'Kota tidak boleh kosong',
            'post.traveler.required' => 'Traveler tidak boleh kosong',
            'post.date.required' => 'Jadwal Perjalanan tidak boleh kosong',
            'post.date.start.required' => 'Tanggal mulai tidak boleh kosong',
            'post.date.end.required' => 'Tanggal akhir tidak boleh kosong',
            'post.duration.required' => 'Durasi tidak boleh kosong',
            'post.title.required' => 'Judul tidak boleh kosong',
            'post.description.required' => 'Deskripsi tidak boleh kosong',
            'post.author.required' => 'Penulis tidak boleh kosong',
            'post.body.required' => 'Isi post tidak boleh kosong',
        ]

        );

        $this->post->fill($request->get('post'))->save();

        Alert::info('You have successfully created a post.');

        return redirect()->route('platform.post.list');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove()
    {
        $this->post->delete();

        Alert::info('You have successfully deleted the post.');

        return redirect()->route('platform.post.list');
    }
}
