<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\Cms;
use App\Traits\ResponseTrait;


class CmspagesController extends Controller
{
    //
    use ResponseTrait;
    public function listItems(Request $request){

        try{
            $sortBy = $request->input('sortBy','id');
            $search = $request->input('search',null); // corrected typo
            $direction = strtoupper($request->input('sortDirection','ASC'));
            $query = Cms::query();
    
            if($search){
                $query->where('title','like','%'. $search . '%');
            }
    
            $query->orderBy($sortBy,$direction);
    
            $cms = $query->get(); // corrected variable name
    
            return $this->successResponse( $cms->toArray() ,'Cms  fetched successfully'); // return the response
    
        }catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
    public function addItems(Request $request){
        try{
            $inputs = $request->all();
            $rules = [
               'title'=>'required',
               'content'=>'required',
            ];
            $validator = Validator::make($inputs,$rules);
            if($validator->fails()){
                return response()->json(['error'=> $validator->errors()->all()],400);
            }

            if(empty($inputs['slug'])){
                $slug = Str::slug($inputs['title']);
            }else{
                $slug = Str::slug($inputs['slug']);
            }
          
            $inputs['slug'] = $slug;
            
            $cms = Cms::create([
                'title' => $inputs['title'],
                'slug' => $inputs['slug'],
                'content' => $inputs['content'],
            ]);
            return response()->json(['cms'=>$cms,'message'=>'Cms '.$cms->title.' Page added Successfully ']);

        }
        catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }

    }
    public function updateItems(Request $request, $id)
    {
        try {
            $cms = Cms::find($id);
            if (!$cms) {
                return response()->json(['error' => 'CMS page not found'], 404);
            }
            $inputs = $request->all();
            $rules = [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ];

            $validator = Validator::make($inputs, $rules);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }

           if(empty($request->slug)){
            $slug = Str::slug($request->title);
           }
           else{
            $slug = Str::slug($request->slug);
           }

            $cms->title = $request->title;
            $cms->slug = $slug;
            $cms->content = $request->content;

            $cms->save();

            return response()->json(['success' => 'CMS page updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the CMS page'], 500);
        }
    }

    public function getItems($id){
        try{
            $cms = Cms::where('id',$id)->first();
            if(!$cms){
                return $this->notFoundRequest("Cms Details Not Found");
            }
            $data = $cms->toArray();
            return $this->successResponse($data,"Cms Details");

        }catch(Exception $ex){
            return $this->responseError($ex);
        }

    }
    public function deleteItems($id){

        try{
            $cms = cms::where('id',$id)->first();
            if(!$cms){
                return $this->sendBadRequest('Cms Page Not Found');
            }
           
            if($cms->delete()){
                return $this->successResponse([],'Cms Page Deleted Successfully',200);
            }
            return $this->sendBadRequest("Bad Request");
        }
        catch (NotFoundHttpException $ex) {
            return $this->notFoundRequest($ex);
        } catch (Exception $ex) {
            return $this->sendErrorResponse($ex);
        }
    }
   
    public function changeStatus(Request $request,$id){
        try{
            $cms = Cms::find($id);
            if($cms){
                $cms->status =  ($cms->status) == '1' ? '0' : '1';
                $cms->save();
                return response()->json(['message'=>'Cms Page Status Updated',200]);
            }
            return response()->json(['message'=>'Cms Page Status Not Updated',404]);
        }
        catch(Exception $ex){
            return $this->sendErrorResponse($ex);
        }
    }
    public function index()
    {
        try{
        $cmsPages = Cms::all();
        return response()->json($cmsPages);
    }catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}

    public function show($slug)
    {
        try{
        $cmsPage = Cms::where('slug', $slug)->first();
        if (!$cmsPage) {
            return response()->json(['error' => 'CMS page not found'], 404);
        }
        return response()->json($cmsPage,200);
    }catch(Exception $ex){
        return $this->sendErrorResponse($ex);
    }
}
}
