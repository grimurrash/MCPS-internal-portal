<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WordCloudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'eventName' => $this->eventName,
            'question' => $this->question,
            'backgroundImage'=> $this->backgroundImage,
            'wordColor'=> $this->wordColor,
            'exceptionWords' => $this->exceptionWords ?? '',
            'pageTitle' => $this->pageTitle,
            'logo' => $this->logo ?? '',
            'isUnique' => $this->isUnique,
            'additionalCss' => $this->additionalCss ?? '',
            'createUser' => UserDataResource::make($this->createUser),
            'answersCount' => count($this->answers),
            'wordCloudUrl' => $this->wordCloudUrl(),
            'answerFormUrl' => $this->answerFormUrl(),
            'fontSizeSmall' => $this->fontSizeSmall,
            'fontSizeLarge' => $this->fontSizeLarge,
            'showCounts' => $this->showCounts,
            'countSize' => $this->countSize,
            'angle' => $this->angle,
        ];
    }
}
