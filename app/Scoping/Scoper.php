<?php
namespace App\Scoping;

use Illuminate\Http\Request;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class Scoper
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder, array $scopes)
    {
        foreach($this->limit($scopes) as $key => $scope){
            if(!$scope instanceof Scope){
                continue;
            }
            $scope->apply($builder, $this->request->get($key));
        }
        return $builder;
    }

    public function limit(array $scopes)
    {
        return array_only(
            $scopes,
            array_keys($this->request->all())
        );
    }
}