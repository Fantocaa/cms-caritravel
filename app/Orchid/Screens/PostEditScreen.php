<?php

namespace App\Orchid\Screens;

use App\Models\cities;
use App\Models\countries;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
// use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
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
    {
        if ($post->exists) {
            $post->date = [
                'start' => $post->start_date,
                'end' => $post->end_date,
            ];
        }

        $post->load('attachment');

        return [
            'post' => $post,
            // 'images' => $post->attachment()->where('group', 'images')->get(),
        ];
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
                ->canSee(!$this->post->exists),

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
                    ->placeholder('Jumlah Traveler')
                    ->type('number'),

                DateRange::make('post.date')
                    ->title('Jadwal Perjalanan')
                    ->placeholder('Pilih Rentang Tanggal'),

                Input::make('post.duration')
                    ->title('Duration')
                    ->type('number')
                    ->placeholder('Enter Duration'),

                Input::make('post.title')
                    ->title('Title')
                    ->placeholder('Attractive but mysterious title')
                    ->help('Specify a short descriptive title for this post.'),

                TextArea::make('post.general_info')
                    ->title('Informasi Umum')
                    ->rows(4)
                    ->maxlength(256)
                    ->placeholder('Informasi Umum terkait Product Travel'),

                Quill::make('post.travel_schedule')
                    ->title('Jadwal Perjalanan')
                    ->placeholder('Informasi Umum terkait Product Travel'),

                TextArea::make('post.additional_info')
                    ->title('Informasi Tambahan')
                    ->rows(4)
                    ->maxlength(255)
                    ->placeholder('Informasi Umum terkait Product Travel'),

                Relation::make('post.author')
                    ->title('Author')
                    ->fromModel(User::class, 'name'),

                Input::make('post.price')
                    ->title('Harga')
                    ->type('number'),

                Upload::make('post.attachment')
                    ->title('Image')
                    ->targetId()
                    ->placeholder('Masukkan Foto'),

                // Quill::make('post.body')
                //     ->title('Main text'),
            ]),
        ];
    }

    public function createOrUpdate(Request $request)
    {
        // dd($request->all());
        $validatedData = $request->validate(
            [
                'post.countries' => 'required',
                'post.cities' => 'required',
                'post.traveler' => 'required|numeric',
                'post.date' => 'required|array',
                'post.date.start' => 'required|date',
                'post.date.end' => 'required|date|after_or_equal:post.date.start',
                'post.duration' => 'required',
                'post.title' => 'required|max:255',
                // 'post.description' => 'required|max:200',
                'post.author' => 'required',
                // 'post.body' => 'required',

            ],
            [
                'post.countries.required' => 'Negara tidak boleh kosong',
                'post.cities.required' => 'Kota tidak boleh kosong',
                'post.traveler.required' => 'Traveler tidak boleh kosong',
                'post.date.required' => 'Jadwal Perjalanan tidak boleh kosong',
                'post.date.start.required' => 'Tanggal mulai tidak boleh kosong',
                'post.date.end.required' => 'Tanggal akhir tidak boleh kosong',
                'post.duration.required' => 'Durasi tidak boleh kosong',
                'post.title.required' => 'Judul tidak boleh kosong',
                // 'post.description.required' => 'Deskripsi tidak boleh kosong',
                'post.author.required' => 'Penulis tidak boleh kosong',
                // 'post.body.required' => 'Isi post tidak boleh kosong',
            ]

        );

        $this->post->fill($request->get('post'))->save();

        // $this->post->attachment()->syncWithoutDetaching($request->input('post.images', []));
        $this->post->attachment()->syncWithoutDetaching(
            $request->input('post.attachment', [])
        );

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

    public function postData()
    {
        $data = Post::with(['attachment', 'country', 'city'])->get();
        $data->transform(function ($post) {
            $attachments = $post->attachment->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'url' => $attachment->url,
                ];
            });

            // Mengubah format tanggal dengan nama bulan dalam bahasa Inggris
            $post->start_date = date('d F Y', strtotime($post->start_date)); // Contoh: 21 May 2024
            $post->end_date = date('d F Y', strtotime($post->end_date)); // Contoh: 23 May 2024

            return [
                'id' => $post->id,
                'title' => $post->title,
                'cities' => $post->city->name,
                'countries' => $post->country->name,
                'traveler' => $post->traveler,
                'duration' => $post->duration,
                'start_date' => $post->start_date,
                'end_date' => $post->end_date,
                'description' => $post->description,
                'body' => $post->body,
                'author' => $post->author,
                'price' => $post->price,
                'attachment' => $attachments,
            ];
        });

        return response()->json($data);
    }
}
