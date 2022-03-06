<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WordCloud extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    const COLOR_MAP = [
        // Sequential Multi-hue Color Map
        'buGn' => 'Sequential Multi-hue Color Map - buGn',
        'buPu' => 'Sequential Multi-hue Color Map - buPu',
        'gnBu' => 'Sequential Multi-hue Color Map - gnBu',
        'orRd' => 'Sequential Multi-hue Color Map - orRd',
        'puBu' => 'Sequential Multi-hue Color Map - puBu',
        'puBuGn' => 'Sequential Multi-hue Color Map - puBuGn',
        'puRd' => 'Sequential Multi-hue Color Map - puRd',
        'rdPu' => 'Sequential Multi-hue Color Map - rdPu',
        'ylGn' => 'Sequential Multi-hue Color Map - ylGn',
        'ylGnBu' => 'Sequential Multi-hue Color Map - ylGnBu',
        'ylOrBr' => 'Sequential Multi-hue Color Map - ylOrBr',
        'ylOrRd' => 'Sequential Multi-hue Color Map - ylOrRd',
        // Sequential Single Color Map
        'blues' => 'Sequential Single Color Map - blues',
        'greens' => 'Sequential Single Color Map - greens',
        'greys' => 'Sequential Single Color Map - greys',
        'oranges' => 'Sequential Single Color Map - oranges',
        'purples' => 'Sequential Single Color Map - purples',
        'reds' => 'Sequential Single Color Map - reds',
        // Diverging Color Ramp
        'brBr' => 'Diverging Color Ramp - brBr',
        'piYG' => 'Diverging Color Ramp - piYG',
        'pRGn' => 'Diverging Color Ramp - pRGn',
        'puOr' => 'Diverging Color Ramp - puOr',
        'rdBu' => 'Diverging Color Ramp - rdBu',
        'rdGy' => 'Diverging Color Ramp - rdGy',
        'rdYlBu' => 'Diverging Color Ramp - rdYlBu',
        'rdYlGn' => 'Diverging Color Ramp - rdYlGn',
        'spectral' => 'Diverging Color Ramp - spectral',
        // Qualitative Color Ramp
        'accent' => 'Diverging Color Ramp - accent',
        'dark2' => 'Diverging Color Ramp - dark2',
        'paired' => 'Diverging Color Ramp - paired',
        'pastel1' => 'Diverging Color Ramp - pastel1',
        'pastel2' => 'Diverging Color Ramp - pastel2',
        'set1' => 'Diverging Color Ramp - set1',
        'set2' => 'Diverging Color Ramp - set2',
        'set3' => 'Diverging Color Ramp - set3',
    ];

    static function getColorsKeys(): array
    {
        return array_keys(self::COLOR_MAP);
    }

    function answers()
    {
        return $this->hasMany(WordCloudAnswer::class, 'wordCloud_id');
    }

    function createUser()
    {
        return $this->belongsTo(User::class, 'createUser_id', 'id');
    }

    function wordCloudUrl(): string
    {
        return 'https://word-cloud.patriotsport.moscow/?question=' . $this->id;
    }

    function answerFormUrl(): string
    {
        return route('wordCloud.answer.show', ['id' => $this->id]);
    }
}
