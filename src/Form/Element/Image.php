<?php

namespace SleepingOwl\Admin\Form\Element;

use Illuminate\Validation\Validator;
use SleepingOwl\Admin\Model\Upload;

class Image extends File
{

    /**
     * @var string
     */
    protected static $route = 'image';

    /**
     * @var array
     */
    protected $uploadValidationRules = ['required', 'image'];

    /**
     * @param Validator $validator
     */
    public function customValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = array_get($validator->attributes(), 'file');

            $size = getimagesize($file->getRealPath());

            if (! $size) {
                $validator->errors()->add('file', trans('sleeping_owl::validation.not_image'));
            }
        });
    }

    /**
     * @param Upload $file
     *
     * @return string
     */
    public function saveFile(Upload $file)
    {
        $settings = $this->getUploadSettings();

        if (class_exists('Intervention\Image\Facades\Image') and (bool) getimagesize($file->file_path)) {
            $image = \Intervention\Image\Facades\Image::make($file->file_path);

            foreach ($settings as $method => $args) {
                call_user_func_array([$image, $method], $args);
            }

            $image->save();
        }

        return parent::saveFile($file);
    }

    /**
     * @param Upload $file
     *
     * @return string
     */
    public function defaultUploadPath(Upload $file)
    {
        return config('sleeping_owl.imagesUploadDirectory', 'images/uploads');
    }

    /**
     * @return array
     */
    public function defaultUploadValidationRules()
    {
        return [
            'file' => 'image',
        ];
    }
}
