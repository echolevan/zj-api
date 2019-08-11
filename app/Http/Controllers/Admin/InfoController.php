<?php

namespace App\Http\Controllers\Admin;

use App\Info;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InfoController extends Controller
{
    public function show($id) {
        $info = DB::table('infos')
            ->where('id', $id)
            ->first();

        return response(['data'=> $info]);
    }

    public function index(Request $request)
    {
        $page = $request->page ?? 1;
        $info = new Info();
        $total = $info->count();
        $list = $info->forPage($page, 10)->latest('num')->latest('created_at')->get();

        $list = $list->map(function ($i, $index) use ($page) {
            $i->rank = ($page - 1) * 10 + $index + 1;
            return $i;
        });
        return response(['data' => [
            'lists' => $list,
            'total' => $total,
        ]]);
    }

    public function store(Request $request)
    {
        $info = new Info();
        $info->name = $request->name;
        $info->image = $request->image;
        $info->info = $request->info;
        $info->num = 0;
        $info->save();

        return response(['msg' => 'success']);
    }

    public function edit($id, Request $request)
    {
        $info = Info::find($id);
        $info->name = $request->name;
        $info->image = $request->image;
        $info->info = $request->info;
        $info->save();

        return response(['msg' => 'success']);
    }

    public function apiGetUploadToken(Request $request)
    {
        $disk = Storage::disk('qiniu');
        $e = explode('.', $request->file_name);
        $e = end($e);
        $name = 'oss/image/' . time() . str_random(15) . '.' . $e;
        $token = $disk->getUploadToken($name);
        return response(['data' => [
            'token' => $token,
            'file_name' => $name
        ]]);
    }


    public function infoEditNum(Request $request)
    {
        DB::table('infos')
            ->where('id', $request->id)
            ->update([
                'num' => $request->value
            ]);
        return response(['msg' => 'success']);
    }

    public function infoDelete($id)
    {
        DB::table('infos')
            ->where('id', $id)
            ->delete();

        return response(['msg' => 'success']);
    }


    public function setting() {
        $setting = DB::table('setting')->first();
        $setting->anonymity = boolval($setting->anonymity) ?? false;
        return response(['data'=> $setting]);
    }

    public function infoEditSetting(Request $request) {
        DB::table('setting')->delete();
        DB::table('setting')->insert([
            'end_at'=> $request->end_at.' 23:59:59',
            'rule'=> $request->rule,
            'anonymity'=> $request->anonymity,
            'visit'=> $request->visit,
        ]);
        return response(['msg' => 'success']);
    }



    public function infoIndex(Request $request) {
        $lists = DB::table('infos')
            ->when(filled($request->search_value), function ($q) {
                $q->where(function ($qq) {
                    $search_value = \request()->search_value;
                    $qq->where('id', $search_value)
                        ->orWhere('name', 'like', "%$search_value%");
                });
            })
            ->get();

        return response(['data'=> $lists]);
    }

    public function infoData() {
        $total = DB::table('infos')->count();

        $setting = DB::table('setting')->first();

        $setting->end_at = strtotime($setting->end_at) * 1000;

        return response(['data'=> [
            'total'=> $total,
            'setting'=> $setting,
        ]]);
    }


    public function infoVisit() {
        $ip = \request()->getClientIp();
        if (
        !DB::table('ip_visit')
            ->where('ip', $ip)
            ->exists()
        ) {
            $setting_id = DB::table('setting')
                ->value('id');
            DB::table('setting')
                ->where('id', $setting_id)
                ->increment('visit');

            DB::table('ip_visit')
                ->insert(['ip'=> $ip]);
        }

        return response(['msg' => 'success']);
    }

    public function infoSubmit(Request $request) {
        // 判断活动结束
        $end_time = DB::table('setting')
            ->value('end_at');
        if (Carbon::now()->toDateTimeString() > $end_time) {
            return response(['status'=> 0, 'msg'=> '投票已经结束']);
        }

        $ip = \request()->getClientIp();
        // 判断该ip 今天有没有提交
        $time = Carbon::now()->toDateString();
        if (
            DB::table('ips')
            ->where('ip', $ip)
            ->where('date', $time)
            ->exists()
        ) {
            return response(['status'=> 0, 'msg'=> '今天已经投过了，请明天继续']);
        }

        DB::table('ips')
            ->insert([
                'ip'=> $ip,
                'date'=> $time
            ]);

        $ids = $request->ids;

        foreach ($ids as $k => $v) {
            DB::table('infos')
                ->where('id', $v)
                ->increment('num');
        }
        return response(['status'=> 1]);
    }

}
