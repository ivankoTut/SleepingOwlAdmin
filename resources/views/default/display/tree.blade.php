<div class="panel panel-default">
    <div class="panel-heading">
        @if ($creatable)
            <a class="btn btn-primary" href="{{ $createUrl }}">
                <i class="fa fa-plus"></i> @lang('sleeping_owl::lang.table.new-entry')
            </a>
        @endif
    </div>

    <table id="page-tree-header" class="table table-primary">
        <thead>
        <tr>
            @foreach ($columns as $column)
                <th {!! $column->getHeader()->htmlAttributesToString() !!}>
                    {!! $column->getHeader()->render() !!}
                </th>
            @endforeach
        </tr>
        </thead>
    </table>
    <ul id="page-tree-list" class="tree-items list-unstyled" data-level="0">
        @include(AdminTemplate::getViewPath('display.partials.tree_children'), ['items' => $items])
    </ul>
</div>

