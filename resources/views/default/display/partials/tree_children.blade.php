@foreach($items as $item)
<li data-id="{{ $item->id }}" >
    <div class="tree-item">
        @if ($item->children->count())
        <i class="fa fa-plus fa-fw"></i>
        @endif

        <div class="columns">
            @foreach ($columns as $column)
                <?php
                $column->setModel($item);
                if($column instanceof \SleepingOwl\Admin\Display\Column\Control) {
                    $column->initialize();
                }
                ?>

                <div {!! $column->htmlAttributesToString() !!}>
                    {!! $column->render() !!}
                </div>
            @endforeach

        </div>
        <div class="clearfix"></div>
    </div>

    @if ($item->children->count() > 0)
        <ul class="list-unstyled">
            @include(AdminTemplate::getViewPath('display.partials.tree_children'), ['items' => $item->children])
        </ul>
    @endif
</li>
@endforeach