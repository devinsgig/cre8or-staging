<?php


namespace Packages\ShaunSocial\Core\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Symfony\Component\HttpFoundation\File\File as FileCore;
use GuzzleHttp\Client;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Library\GetMostCommonColors;

class File
{
    public function storePhoto($file, $params = [])
    {
        if (is_string($file)) {
            $file = new FileCore($file);
        }

        //disable resize file
        $extension = $file->getExtension();
		if ($file instanceof UploadedFile) {
			$params['extension'] = $extension = $file->getClientOriginalExtension();
			$params['name'] = $file->getClientOriginalName();
		}        
        $fileTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
        $disableResize = in_array($extension, ['gif', 'ico', 'svg']);       
        if (! $disableResize) {
            try {
                $image = InterventionImage::make($file);
            } catch (\Exception $e) {
                $fileName = $file->getFilename();
                if (!empty($params['name'])) {
                    $fileName = $params['name'];
                }
                throw new MessageHttpException(__("The :file is in a format that we don't support.", ['file' => $fileName])); 
            }
    
            $image->orientate();
            $maxWidth = config('shaun_core.file.photo.max_width');
            $maxHeight = config('shaun_core.file.photo.max_height');

            if ($image->width() > $maxWidth) {
                $image->resize($maxWidth, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            if ($image->height() > $maxHeight) {
                $image->resize(null, $maxHeight, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            if (! empty($params['resize_size'])) {
                $this->resizeImage($image, $params['resize_size']);
            }

            //move file to tmp
            $image->save($fileTmp);
            $mostCollors = new GetMostCommonColors();
            $colors = $mostCollors->Get_Color($fileTmp, 10, true, true, 24);
            $dominantColor = '#'.converBlurColor(array_key_first($colors));
            $storageFile = $this->store(new FileCore($fileTmp), array_merge($params, [
                'params' => json_encode([
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'dominant_color' => $dominantColor
                ]),
            ]), false);

            if (! empty($params['resize_sizes']) && is_array($params['resize_sizes'])) {
                $params['parent_file_id'] = $storageFile->id;
                $childs = [];
                foreach ($params['resize_sizes'] as $type => $size) {
                    $imageResize = InterventionImage::make($fileTmp);
                    $this->resizeImage($imageResize, $size);

                    $fileResizeTmp = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
                    $imageResize->save($fileResizeTmp);
                    $params['type'] = $type;
                    $childs[] = $this->store(new FileCore($fileResizeTmp), array_merge($params, [
                        'params' => json_encode([
                            'width' => $imageResize->width(),
                            'height' => $imageResize->height(),
                            'dominant_color' => $dominantColor
                        ]),
                    ]));
                }

                $storageFile->update(['has_child' => true]);
                $storageFile->setChilds(collect($childs));
            }

        } else {
            copy($file->getRealPath(), $fileTmp);

            $storageFile = $this->store(new FileCore($fileTmp), array_merge($params, [
                'params' => json_encode([
                    'width' => 0,
                    'height' => 0,
                ]),
            ]), false);
        }

        //unlink
        unlink($fileTmp);
        unlink($file->getRealPath());

        return $storageFile;
    }

    public function store(FileCore $file, $params = [], $isDelete = true)
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');

        $extension = $file->getExtension();
        if (!empty($params['extension'])) {
            $extension = $params['extension'];
        }
        $name = Str::uuid().'.'.$extension;

        $fileName = $file->getFilename();
        if (!empty($params['name'])) {
            $fileName = $params['name'];
        }

        if (empty($params['parent_type'])) {
            $params['parent_type'] = 'default';
        }
        
        $path = 'files/'.$params['parent_type'].'/'.$year.'/'.$month.'/'.$day.'/'.$name;
        if (env('FILESYSTEM_CLOUD')) {
            $path = env('APP_NAME').'/'.$path;
        }

        $result = Storage::put($path, $file->getContent());
        if (! $result) {
            throw new MessageHttpException(__('Error when store file.')); 
        }
        $data = array_merge([
            'service_key' => config('filesystems.default'),
            'extension' => $extension,
            'name' => $fileName,
            'storage_path' => $path,
            'size' => $file->getSize(),
        ], $params);

        if ($isDelete) {
            unlink($file->getRealPath());
        }
        $storageFile = StorageFile::create($data);
        StorageFile::setCacheQueryFieldsResult('id', $storageFile->id, $storageFile);
        
        return $storageFile;
        
    }

    protected function resizeImage($image, $data)
    {
        $real = ! empty($data['real']) ? $data['real'] : false;
        $mode = ! empty($data['mode']) ? $data['mode'] : 'resize';
        $width = ! empty($data['width']) ? $data['width'] : null;
        $height = ! empty($data['height']) ? $data['height'] : null;
        $keep = ! empty($data['keep']) ? $data['keep'] : false;

        if ($keep) {
            if ($image->width() > $width) {
                $image->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            if ($image->height() > $height) {
                $image->resize(null, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            return;
        }

        $image->{$mode}($width, $height, function ($constraint) use ($real) {
            if (! $real) {
                $constraint->aspectRatio();
            }
        });
    }

    public function downloadPhoto($url)
    {
        try {
            $options = getClientOptions();
            $client = new Client();
            $response = $client->get($url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {                            
                $content = $response->getBody()->getContents();
                $image = InterventionImage::make($content);
                $extension = '';
                switch ($image->mime()) {
                    case 'image/jpeg':
                        $extension = 'jpg';
                        break;
                    case 'image/png':
                        $extension = 'png';
                        break;
                    case 'image/gif':
                        $extension = 'gif';
                        break;
                    case 'image/webp':
                        $extension = 'webp';
                        break;
                }
                if ($extension) {
                    $photoPath = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$extension;
                    if (in_array($extension, ['gif'])) {
                        file_put_contents($photoPath, $content);
                    } else {
                        $image->save($photoPath);
                    }
    
                    return $photoPath;
                }
            }
        } catch (Exception $e) {
        }

        return null;
    }

    public function downloadFile($url)
    {
        try {
            $options = getClientOptions();
            $client = new Client();
            $response = $client->get($url, $options);
            $statusCode = $response->getStatusCode();
            if ($statusCode == 200) {                            
                $content = $response->getBody()->getContents();
                $info = pathinfo($url);
                $path = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.'.$info['extension'];
                file_put_contents($path, $content);

                return $path;
            }
        } catch (Exception $e) {
        }

        return null;
    }
}
