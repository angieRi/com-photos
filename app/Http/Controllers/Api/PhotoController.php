<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use function PHPUnit\Framework\isEmpty;

class PhotoController extends Controller
{
    private $photos;

    /**
     * Conecta con la Api fotos
     */
    public function __construct()
    {
        $this->photos = Http::get('https://jsonplaceholder.typicode.com/photos');

    }

    /**
     * Verifica la conexion de la url ,Api datos de fotos
     * @return \Illuminate\Http\JsonResponse, retorna el estado de conexion.
     */
    public function conexion()
    {
         $statu = $this->photos->status();
        if ($statu < 200 & $statu > 202) {
            $reconexion = Http::retry(3, 100, function ($exception) {
                return $exception instanceof ConnectionException;
            })->post('https://jsonplaceholder.typicode.com/photos')->status();

            return response()->json([
                'statu' =>$reconexion,
                'message' =>'Internal Server Error.'
            ]);
        }else{
            return response()->json([
                'statu' =>$statu,
                'message' =>'Server connected.'
            ]);
        }
    }

    /**
     * Guarda en la base de datos los valos obtenidos de la Api fotos
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveItems()
    {
        $statu=$this->conexion()->isSuccessful();
        $array_photos=$this->photos->json();

        if(!empty($array_photos) && $statu)
        {
            foreach ($array_photos as $photo){
              [$c_photo,$v_photo] = Arr::divide($photo);

                $photo_new = New Photo();
                $photo_new->albumId = $v_photo[0];
                $photo_new->id = $v_photo[1];
                $photo_new->title = $v_photo[2];
                $photo_new->url = $v_photo[3];
                $photo_new->thumbnailUrl = $v_photo[4];
                $photo_new->save();

            }
            return response()->json([
                'statu' =>200,
                'message' =>'Data stored correctly.'
            ]);
        }else{
            return response()->json([
                'statu' =>400,
                'message' =>'error, failed to save data.'
            ]);
        }

    }
}
