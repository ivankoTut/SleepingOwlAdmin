<?php

namespace SleepingOwl\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Upload
 * @package SleepingOwl\Admin\Model
 *
 * @property int $size File size
 * @property string $file File path
 * @property string $file_path File full path
 * @property string $file_url File url
 * @property string $filename File name
 * @property string $ext File extension
 * @property string mime File mime type
 */
class Upload extends Model
{

    use \KodiComponents\Support\Upload;

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Upload $file) {
            /** @var \Illuminate\Http\UploadedFile $uploadedFile */
            $uploadedFile = $file->file;

            $file->filename = $uploadedFile->getClientOriginalName();
            $file->size     = $uploadedFile->getSize();
            $file->ext      = $uploadedFile->getClientOriginalExtension();
            $file->mime     = $uploadedFile->getMimeType();
        }, -100);
    }

    /**
     * Move file into a directory and remove it from database
     *
     * @param string $file
     * @param string $to
     *
     * @return bool
     */
    public static function pull($file, $to)
    {
        $file = Upload::whereFile($file)->first();

        if (is_null($file)) {
            return false;
        }

        $file->move($to);

        return true;
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'file_path',
        'file_url',
    ];

    /**
     * @param string $directory
     * @param string|null $name
     *
     * @return mixed
     */
    public function move($directory, $name = null)
    {
        $path = is_null($name) ? $directory : rtrim($directory, '/\\').'/'.$name;
        \File::move($this->file_path, $path);

        $this->delete();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sleeping_owl_upload';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['file'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'file' => 'file',
    ];
}