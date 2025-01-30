<?php
namespace App\Helpers;

class Helper
{
    static public function getNumber(): int
    {
        $page = request()->get('page', 1);
        $limit = request()->get('limit', 10);
        if ($page > 0 && $limit > 0) {
            $page = $page / $limit;
        }
        return ($page * $limit) + 1;
    }

    static function generateDatatable(array $data, array $params)
    {
        $request = request();
        $data = $data['data']['data'];
        $paging = $data['data']['meta'];
        $data["data"] = $data;
        $data["draw"] = $request->get('draw');
        $data["recordsTotal"] = $paging['total'];
        $data["recordsFiltered"] = $paging['total'];
        $data["params"] = $params;
        return $data;
    }
}