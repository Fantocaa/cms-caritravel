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
use Orchid\Screen\Fields\Group;
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
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        // return 'PostEditScreen';
        return $this->post->exists ? 'Edit post' : 'Creating a new Package';
    }

    public function description(): ?string
    {
        return 'Travel Package';
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
            Layout::columns([
                Layout::rows([

                    Relation::make('post.country_ids')
                        ->fromModel(countries::class, 'name')
                        ->multiple()
                        ->title('Negara')
                        ->placeholder('Enter Negara'),

                    Relation::make('post.city_ids')
                        ->fromModel(cities::class, 'name')
                        ->multiple()
                        ->title('Kota')
                        ->placeholder('Enter Kota')
                        ->dependsOn('post.country_ids'),

                    Input::make('post.traveler')
                        ->title('Traveler')
                        ->placeholder('Jumlah Traveler')
                        ->type('number'),

                    DateRange::make('post.date')
                        ->title('Jadwal Perjalanan')
                        ->placeholder('Pilih Rentang Tanggal'),

                    Group::make([
                        Input::make('post.duration')
                            ->title('Durasi Hari')
                            ->type('number')
                            ->placeholder('Enter Duration'),

                        Input::make('post.duration_night')
                            ->title('Durasi Malam')
                            ->type('number')
                            ->placeholder('Enter Duration'),
                    ]),

                    Relation::make('post.author')
                        ->title('Sales')
                        ->fromModel(User::class, 'name'),

                    Input::make('post.price')
                        ->title('Harga')
                        ->type('number'),

                    Upload::make('post.attachment')
                        ->title('Image')
                        ->targetId()
                        ->placeholder('Masukkan Foto'),

                ])->title('Informasi Paket'),

                Layout::rows([
                    Input::make('post.title')
                        ->title('Title')
                        ->placeholder('Attractive but mysterious title')
                        ->help('Specify a short descriptive title for this post.'),

                    TextArea::make('post.general_info')
                        ->title('Informasi Umum')
                        ->rows(8)
                        ->maxlength(255)
                        ->placeholder('Informasi Umum terkait Product Travel'),

                    Quill::make('post.travel_schedule')
                        ->title('Jadwal Perjalanan')
                        ->placeholder('Informasi Umum terkait Product Travel'),

                    TextArea::make('post.additional_info')
                        ->title('Informasi Tambahan')
                        ->rows(8)
                        ->maxlength(255)
                        ->placeholder('Informasi Umum terkait Product Travel'),

                ])->title('Konten Paket'),
            ]),
        ];
    }

    public function createOrUpdate(Request $request)
    {
        // dd($request)->all();
        $validatedData = $request->validate([
            'post.country_ids' => 'required',
            'post.city_ids' => 'required',
            'post.traveler' => 'required|numeric',
            'post.date' => 'required|array',
            'post.date.start' => 'required|date',
            'post.date.end' => 'required|date|after_or_equal:post.date.start',
            'post.duration' => 'required',
            'post.duration_night' => 'required',
            'post.title' => 'required|max:255',
            'post.author' => 'required',
        ], [
            'post.country_ids.required' => 'Negara tidak boleh kosong',
            'post.city_ids.required' => 'Kota tidak boleh kosong',
            'post.traveler.required' => 'Traveler tidak boleh kosong',
            'post.date.required' => 'Jadwal Perjalanan tidak boleh kosong',
            'post.date.start.required' => 'Tanggal mulai tidak boleh kosong',
            'post.date.end.required' => 'Tanggal akhir tidak boleh kosong',
            'post.duration.required' => 'Durasi Hari tidak boleh kosong',
            'post.duration_night.required' => 'Durasi Malam tidak boleh kosong',
            'post.title.required' => 'Judul tidak boleh kosong',
            'post.author.required' => 'Penulis tidak boleh kosong',
        ]);

        // Ambil ID negara dan kota dari request
        $countryIds = $request->input('post.country_ids');
        $cityIds = $request->input('post.city_ids');

        // Isi model post dengan data dari request termasuk ID negara dan kota
        $postData = $request->get('post');
        $postData['countries'] = $countryIds;
        $postData['cities'] = $cityIds;

        $this->post->fill($postData)->save();

        // Sync attachment
        $this->post->attachment()->syncWithoutDetaching(
            $request->input('post.attachment', [])
        );

        Alert::info('You have successfully created a post.');

        return redirect()->route('platform.post.list');
    }

    public function postData()
    {
        $data = Post::with(['attachment'])->get();

        $transformedData = $data->map(function ($post) {
            $cityIds = is_string($post->cities) ? json_decode($post->cities, true) : $post->cities;
            $countryIds = is_string($post->countries) ? json_decode($post->countries, true) : $post->countries;

            $cityNames = collect($cityIds)->map(function ($cityId) {
                $city = cities::find($cityId);
                return $city ? $city->name : null;
            })->filter();

            $countryNames = collect($countryIds)->map(function ($countryId) {
                $country = countries::find($countryId);
                return $country ? $country->name : null;
            })->filter();

            $attachment = $post->attachment->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'url' => $attachment->url,
                ];
            });

            $formattedStartDate = date('d F Y', strtotime($post->start_date));
            $formattedEndDate = date('d F Y', strtotime($post->end_date));

            $author = User::find($post->author);
            $whatsappLink = $author ? "https://api.whatsapp.com/send?phone=" . preg_replace('/[^0-9]/', '', $author->phone) : null;

            $formattedPrice = number_format($post->price, 0, ',', '.');

            return [
                'id' => $post->id,
                'title' => $post->title,
                'cities' => $cityNames->implode(', '),
                'city_ids' => $cityIds,
                'countries' => $countryNames->implode(', '),
                'country_ids' => $countryIds,
                'traveler' => $post->traveler,
                'duration' => $post->duration,
                'duration_night' => $post->duration_night,
                'start_date' => $formattedStartDate,
                'end_date' => $formattedEndDate,
                'whatsapp_link' => $whatsappLink,
                'price' => $formattedPrice,
                'attachment' => $attachment,
                'general_info' => $post->general_info,
                'travel_schedule' => $post->travel_schedule,
                'additional_info' => $post->additional_info,
            ];
        });

        return response()->json($transformedData);
    }


    // public function postDatadb()
    // {
    //     $data = Post::all();
    //     return response()->json($data);
    // }

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
