<?php

namespace App\Http\Controllers\Admin\Communicate;

use App\AramiscTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AramiscTemplateController extends Controller
{

    public function __construct()
	{
        $this->middleware('PM');
        // User::checkAuth();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AramiscTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(AramiscTemplate $smsTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AramiscTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(AramiscTemplate $smsTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AramiscTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AramiscTemplate $smsTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AramiscTemplate  $smsTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(AramiscTemplate $smsTemplate)
    {
        //
    }
}
