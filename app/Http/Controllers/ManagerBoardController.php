<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Carbon\Carbon;
use Microsoft\Graph\Model;
use App\Models\Board;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Graph;
use SimpleXMLElement;
use Exception;

class ManagerBoardController extends Controller
{
    public function show($id)
    {
        $data = [
            'fio' => 'Сабиров Рашит Алмазович',
            'post' => 'Программист',
            'office' => '314',
            'travel-time' => [
                [
                    'name' => 'ОШ',
                    'time' => '0 мин',
                    'distance' => '0 км'
                ]
            ]
        ];
        $board = Board::find($id);
        if ($board !== null) {
            $data = [
                'id' => $id,
                'fio' => $board->userName ?? 'Неизвестно',
                'post' => $board->position ?? 'Неизвестно',
                'office' => $board->officeNumber ?? '-',
                'building' => [
                    'name' => $board->building->name,
                    'lat' => $board->building->lat,
                    'lon' => $board->building->lon,
                ],
            ];
        }
        return view('board', $data);
    }

    public function yandexWeather(Request $request): Response
    {
        $url = 'https://api.weather.yandex.ru/v2/forecast';
        $apiKey = '6fd4767d-5e31-47cd-9c59-c9b3f07f06a5';

        return Http::withHeaders([
            'content-type' => 'application/json',
            'Accept' => 'application/json',
            'X-Yandex-API-Key' => $apiKey
        ])->get($url, [
            'lat' => $request->input('lat'),
            'lon' => $request->input('lan'),
            'lang' => 'ru',
            'limit' => '4',
            'extra' => 'false',
            'hours' => 'false'
        ]);
    }


    public function getDistanceFromBoardBeforeOthersBuildings(Request $request): JsonResponse
    {
        $board = Board::find($request->input('boardId'));
        $othersBuildings = Building::othersBuildings($board->building->id);
        $travelTimes = [];
        $apiKey = config('google.distance_matrix_api_key');
        $originsAddress = $board->building->lat . ',' . $board->building->lon;
        foreach ($othersBuildings as $building) {
            $destinationsAddress = $building->lat . ',' . $building->lon;
            $res = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=$originsAddress&destinations=$destinationsAddress&key=$apiKey&language=ru_RU"), true);
            if ($res['status'] === 'OK') {
                $travelTimes[] = [
                    'name' => $building->name,
                    'lat' => $building->lat,
                    'lon' => $building->lon,
                    'distance' => $res['rows'][0]['elements'][0]['distance']['text'],
                    'duration' => $res['rows'][0]['elements'][0]['duration']['text']
                ];
            }
        }


        return response()->json($travelTimes);
    }

    /**
     * @throws \Exception
     */
    public function getNews(Request $request): JsonResponse
    {
        $postNumber = (int)$request->input('number', 0);
        $url = $request->input('newsUrl', '');
        if ($url === '') return response()->json()->setStatusCode(400);
        $nextNumber = $postNumber + 1;
        $rss = file_get_contents($url);
        $xml = new SimpleXMLElement($rss, LIBXML_NOCDATA);
        $post = $xml->channel->item[$postNumber];
        $now = new DateTime();
        $pubDate = new DateTime((string)$post->pubDate);
        $diff = $pubDate->diff($now);
        if ($diff->d > 3) {
            $post = $xml->channel->item[0];
            $pubDate = new DateTime((string)$post->pubDate);
            $diff = $pubDate->diff($now);
            $nextNumber = 1;
        }
        return response()->json([
            'title' => (string)$post->title,
            'description' => (string)$post->description,
            'link' => (string)$post->link,
            'dayDiff' => [
                'days' => $diff->d,
                'hour' => $diff->h,
                'minute' => $diff->i
            ],
            'nextNumber' => $nextNumber
        ]);
    }

    private function getGraph($board): Graph
    {
        // Get the access token from the cache
        $accessToken = $board->getAccessToken();

        // Create a Graph client
        $graph = new Graph();
        $graph->setAccessToken($accessToken);
        return $graph;
    }

    public function getCalendar(Request $request): JsonResponse
    {
        $board = Board::find($request->input('boardId'));
        $graph = $this->getGraph($board);

        $startDateTime = Carbon::now()->format('Y-m-d');
        $endDateTime = Carbon::today()->addDays(7)->format('Y-m-d');
        $calendar = $graph->createRequest('GET', "/me/calendarview?startdatetime=$startDateTime&enddatetime=$endDateTime")
            ->setReturnType(Model\Calendar::class)
            ->execute();
        $data = [];
        foreach ($calendar as $item) {
            $item = $item->getProperties();
            $endDate = Carbon::parse($item['end']['dateTime'])->addHours(3);
            if ($endDate < Carbon::now()) continue;

            $startDate = Carbon::parse($item['start']['dateTime'])->addHours(3);
            $title = $item['subject'];
            $location = $item['location']['displayName'];
            $data[$startDate->format('Y-m-d')][] = [
                'title' => $title,
                'office' => $location,
                'startTime' => $startDate->format('H:i'),
                'endTime' => $endDate->format('H:i'),
            ];
        }

        return response()->json($data);
    }

    public function microsoftSignIn(): RedirectResponse
    {
        // Initialize the OAuth client
        $oauthClient = new GenericProvider([
            'clientId' => config('azure.appId'),
            'clientSecret' => config('azure.appSecret'),
            'redirectUri' => config('azure.redirectUri'),
            'urlAuthorize' => config('azure.authority') . config('azure.authorizeEndpoint'),
            'urlAccessToken' => config('azure.authority') . config('azure.tokenEndpoint'),
            'urlResourceOwnerDetails' => '',
            'scopes' => config('azure.scopes')
        ]);

        $authUrl = $oauthClient->getAuthorizationUrl();
        // Save client state so we can validate in callback
        session(['oauthState' => $oauthClient->getState()]);

        // Redirect to AAD signin page
        return redirect()->away($authUrl);
    }

    public function microsoftCallback(Request $request)
    {
        // Validate state
        $expectedState = session('oauthState');
        $request->session()->forget('oauthState');
        $providedState = $request->query('state');
        if (!isset($expectedState)) {
            // If there is no expected state in the session,
            // do nothing and redirect to the home page.
            return redirect('/');
        }

        if (!isset($providedState) || $expectedState !== $providedState) {
            return redirect('/')
                ->with('error', 'Invalid auth state')
                ->with('errorDetail', 'The provided auth state did not match the expected value');
        }

        // Authorization code should be in the "code" query param
        $authCode = $request->query('code');
        if (isset($authCode)) {
            // Initialize the OAuth client
            $oauthClient = new GenericProvider([
                'clientId' => config('azure.appId'),
                'clientSecret' => config('azure.appSecret'),
                'redirectUri' => config('azure.redirectUri'),
                'urlAuthorize' => config('azure.authority') . config('azure.authorizeEndpoint'),
                'urlAccessToken' => config('azure.authority') . config('azure.tokenEndpoint'),
                'urlResourceOwnerDetails' => '',
                'scopes' => config('azure.scopes')
            ]);

            try {
                // Make the token request
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $authCode
                ]);

                $graph = new Graph();
                $graph->setAccessToken($accessToken->getToken());

                $user = $graph->createRequest('GET', '/me?$select=displayName,mail,mailboxSettings,userPrincipalName')
                    ->setReturnType(Model\User::class)
                    ->execute();

                $board = Board::where('userEmail', $user->getMail() ?? $user->getUserPrincipalName())->first();
                $board->storeToken($accessToken);

                return redirect('/');
            } catch (IdentityProviderException | GuzzleException $e) {
                return redirect('/')
                    ->with('error', 'Error requesting access token')
                    ->with('errorDetail', $e->getMessage());
            } catch (Exception $e) {
                return redirect('/')
                    ->with('error', 'Error requesting access token')
                    ->with('errorDetail', $e->getMessage());
            }
        }

        return redirect('/')
            ->with('error', $request->query('error'))
            ->with('errorDetail', $request->query('error_description'));
    }

    public function microsoftSignOut($id)
    {
        $board = Board::find($id);
        $board->clearTokens();
        return redirect('/');
    }
}
