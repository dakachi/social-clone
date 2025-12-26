<?php

namespace Modules\AppAIPrompts\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\AppAIPrompts\Models\AIPrompt;

class AppAIPromptsController extends Controller
{
    public function __construct()
    {
        $this->table = 'ai_prompts';
    }

    public function list(Request $request)
    {
        $result = AIPrompt::query()
            ->where('team_id', $request->team_id)
            ->orderByDesc('id')
            ->paginate(3000);

        ms([
            'status' => 1,
            'data'   => view('appaiprompts::list', compact('result'))->render(),
        ]);
    }

    public function update(Request $request)
    {
        $result = AIPrompt::where('id_secure', $request->id)->first();

        ms([
            'status' => 1,
            'data'   => view('appaiprompts::update', compact('result'))->render(),
        ]);
    }

    public function save(Request $request)
    {
        $item = AIPrompt::where('id_secure', $request->id)->first();

        $rules = [
            'prompt' => ['required'],
        ];

        if ($item) {
            $rules['prompt'][] = Rule::unique($this->table, 'prompt')->ignore($item->id);
        } else {
            $rules['prompt'][] = Rule::unique($this->table, 'prompt');
        }

        $validator = Validator::make($request->all(), $rules);
        if (! $validator->passes()) {
            return ms([
                'status'  => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        if ($item) {
            $item->update([
                'prompt' => $request->input('prompt'),
            ]);
        } else {
            AIPrompt::create([
                'id_secure' => rand_string(),
                'team_id'   => $request->team_id,
                'prompt'    => $request->input('prompt'),
            ]);
        }

        ms(['status' => 1, 'message' => 'Succeed']);
    }

    public function destroy(Request $request)
    {
        $id_arr = id_arr($request->input('id'));
        if (empty($id_arr)) {
            ms(['status' => 0, 'message' => __('Please select at least one item')]);
        }

        AIPrompt::whereIn('id_secure', $id_arr)->delete();

        ms(['status' => 1, 'message' => __('Succeed')]);
    }
}