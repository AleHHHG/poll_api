<?php

namespace App\Http\Controllers;

use App\Poll;
use App\Option;
use App\Http\Resources\PollResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    public function show($id)
    {
        $poll = Poll::findOrFail($id);
        $poll->increment('views');
        return new PollResource($poll);
    }

    public function store(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'poll_description' => 'required|string',
            'options' => 'required|array',
        ]);
	    if ($validator->fails()) {
	    	return response()->json(['error' => 'Bad Request'], 400);
	    }
    	$poll = Poll::create(['description' => $request->poll_description]);
    	$poll->options()->createMany(array_map(function($value){
    		return array('description'=> $value);
    	}, $request->options));
    	return response()->json(['poll_id' => $poll->id], 201);
    }

    public function vote(Request $request, $id)
    {
    	$poll = Poll::findOrFail($id);
    	$validator = Validator::make($request->all(), [
            'option_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
	    	return response()->json(['error' => 'Bad Request'], 400);
	    }
	    $option = $poll->options->where('id',$request->option_id)->first();
	    if ($option){
	    	$option->increment('votes');
	    	return response()->json(['votes' => $option->votes], 200);
	    }else{
	    	return response()->json(['error' => 'Option not found'], 404);
	    }
    }

    public function stats($id)
    {
    	$poll = Poll::findOrFail($id);
    	return response()->json(
    		[
    			'views' => $poll->views,
    			'votes' => array_map(function($value){
    				return array(
    					'option_id' => $value['id'], 'votes' => $value['votes']
    				);
    			},$poll->options->toArray())
    		], 200);
    }
}
