<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class HelpAndSupportController extends BaseController
{
    public function getFaqs(){
        $userType = auth('api')->user()->getRoleNames()->first();
        $faqs = Faq::where('type', $userType)->get();
        return $this->sendResponse($faqs, 'Faqs retrieved successfully.');
    }

    public function getSingleFaq($id){
        $userType = auth('api')->user()->getRoleNames()->first();
        $faq = Faq::where('type', $userType)->where('id', $id)->first();
        if(!$faq){
            return $this->sendError('FAQ not found');
        }
        return $this->sendResponse($faq, 'FAQ retrieved successfully.');
    }
}
