<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::paginate(10);
        return view('faq.index')->with('faqs', $faqs);
    }

    public function create()
    {
        return view('faq.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);
        if($request->has('id')){
            $faq = Faq::find($request->id);
            if(!$faq) {
                return redirect()->route('admin.faqs.index')->with('error', 'FAQ not found.');
            }
            $faq->update($request->all());
        }else {
            $faq = Faq::create($request->all());
        }
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }
    public function edit($id){
        $faq = Faq::find($id);
        return view('faq.edit')->with('faq', $faq);
    }
    public function destroy($id){
        $faq = Faq::find($id);
        if(!$faq) {
            return redirect()->route('admin.faqs.index')->with('error', 'FAQ not found.');
        }
        $faq->delete();
        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }
}
