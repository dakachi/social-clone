<?php

namespace Modules\AdminPlans\Services;

use Modules\AdminPlans\Facades\Plan;
use Modules\AdminPlans\Models\Plans as PlanModel;

class PricingService
{
    protected $features = [];
    protected $subfeatures = [];

    public function add(array $feature)
    {
        if (isset($feature[0]) && is_array($feature[0])) {
            foreach ($feature as $f) {
                $this->add($f); 
            }
            return;
        }
        if (empty($feature['key'])) return;
        $this->features[$feature['key']] = $feature;
    }

    public function addSubFeatures(array $feature)
    {
        if (isset($feature[0]) && is_array($feature[0])) {
            foreach ($feature as $f) {
                $this->addSubFeatures($f); 
            }
            return;
        }
        if (empty($feature['key'])) return;
        $this->subfeatures[$feature['key']] = $feature;

        usort($this->subfeatures, function($a, $b) {
            return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
        });
    }

    public function getSubFeatures($parent = null)
    {
        $subfeatures = array_values($this->subfeatures);

        if ($parent) {
            $subfeatures = array_filter($subfeatures, function ($item) use ($parent) {
                return isset($item['parent']) && $item['parent'] == $parent;
            });
            $subfeatures = array_values($subfeatures);
        }

        usort($subfeatures, function($a, $b) {
            return ($b['sort'] ?? 0) <=> ($b['sort'] ?? 0);
        });

        return $subfeatures;
    }

    public function all()
    {
        $features = array_values($this->features);
        usort($features, function($a, $b) {
            return ($b['sort'] ?? 0) <=> ($a['sort'] ?? 0);
        });

        return $features;
    }

    public function render($feature, $permissions, $value = null)
    {
        // Kiểm tra key tồn tại và check trạng thái
        $isCheck = true;
        if (!array_key_exists('key', $feature)) {
            $isCheck = true;
        } elseif (isset($feature['check']) && $feature['check'] === false) {
            $isCheck = true;
        } else {
            $isCheck = self::hasPermissionKey($permissions, $feature['key']);
        }

        if ($value === null && array_key_exists('key', $feature)) {
            $value = $permissions[$feature['key']] ?? null;
        }

        $subfeatures = [];
        if (!empty($feature['subfeatures'] ?? [])) {
            foreach ($feature['subfeatures'] as $sub) {
                $subfeatures[] = $this->render($sub, $permissions, $permissions[$sub['key']] ?? null);
            }
        }

        return [
            'check'      => $isCheck,
            'label'      => $feature['label'] ?? '',
            'key'        => $feature['key'] ?? null,
            'raw'        => $value,
            'type'       => $feature['type'] ?? 'boolean',
            'subfeature' => $subfeatures,
            'feature'    => $feature,
        ];
    }

    public function getListForPlan($plan)
    {
        $permissions = $plan['permissions'] ?? $plan->permissions ?? [];

        $build = function($features) use (&$build, $permissions) {
            $list = [];
            foreach ($features as $feature) {
                $value = $permissions[$feature['key']] ?? null;
                $subList = [];
                if (!empty($feature['subfeature'])) {
                    $subList = $this->groupSubFeaturesByTab($feature['subfeature'], $permissions, $build);
                }
                $item = $this->render($feature, $permissions, $value);
                $item['subfeature'] = $subList;
                $list[] = $item;
            }

            usort($list, function($a, $b) {
                return ($a['feature']['sort'] ?? 0) <=> ($b['feature']['sort'] ?? 0);
            });

            return $list;
        };

        return $build($this->features);
    }

    
    protected function groupSubFeaturesByTab(array $subfeatures, $permissions, $build)
    {
        $grouped = [];
        $other = [];

        foreach ($subfeatures as $sub) {
            if (isset($sub['tab_id']) && isset($sub['tab_name'])) {
                $grouped[$sub['tab_id']]['tab_id'] = $sub['tab_id'];
                $grouped[$sub['tab_id']]['tab_name'] = $sub['tab_name'];
                $grouped[$sub['tab_id']]['items'][] = $build([$sub])[0];
            } else {
                $other[] = $build([$sub])[0];
            }
        }

        // Sort by tab_id tăng dần
        ksort($grouped);

        $result = [];
        foreach ($grouped as $g) {
            $result[] = [
                'tab_id'   => $g['tab_id'],
                'tab_name' => $g['tab_name'],
                'items'    => $g['items'],
            ];
        }

        if (!empty($other)) {
            $result[] = [
                'tab_id'   => 99999,
                'tab_name' => __('Other'),
                'items'    => $other,
            ];
        }

        return $result;
    }

        
    public function plansWithFeatures($plan_id = false)
    {
        $typeKeys = array_keys(Plan::getTypes());

        $query = PlanModel::where('status', 1);

        if($plan_id){
            $query->where("id", $plan_id);
        }

        $plans = $query->orderByDesc('position')->get();

        $grouped = [];


        foreach ($plans as $plan) {
            $typeKey = $plan->type;

            if (!isset($grouped[$typeKey])) {
                $grouped[$typeKey] = [];
            }

            $planItem = [
                'id'          => $plan->id,
                'id_secure'   => $plan->id_secure,
                'name'        => $plan->name,
                'price'       => $plan->price,
                'desc'        => $plan->desc,
                'trial_day'   => $plan->trial_day,
                'free_plan'   => $plan->free_plan,
                'featured'    => $plan->featured,
                'position'    => $plan->position,
                'permissions' => $plan->permissions,
                'features'    => $this->getListForPlan($plan),
            ];

            if($plan_id) return $planItem;

            $grouped[$typeKey][] = $planItem;
        }

        $final = [];
        foreach ($typeKeys as $k) {
            $final[$k] = $grouped[$k] ?? [];
        }

        return $final;
    }

    public static function hasPermissionKey($permissions, $key)
    {
        if (is_array($permissions)) {
            if (array_key_exists($key, $permissions)) {
                return true;
            }

            foreach ($permissions as $item) {
                if (isset($item['key']) && $item['key'] === $key) {
                    return true;
                }
            }
        }
        return false;
    }
}
