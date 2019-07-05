<div class="flex flex-wrap mb-4">
    @if(isset($left) && isset($left['url']) && isset($left['text']))
        <div class="flex justify-start text-sm w-1/2 text-grey-dark">
            <a class="no-underline hover:underline" style="color: #8795A1" href="{{$left['url']}}">
                <span style="color: #8795A1">&#8592;</span>&nbsp;&nbsp;{{$left['text']}}
            </a>
        </div>
    @endif
    @if(isset($right) && isset($right['url']) && isset($right['text']))
        <div class="flex justify-end text-sm w-1/2 text-grey-dark">
            <a class="no-underline hover:underline" style="color: #8795A1" href="{{$right['url']}}">
                {{$right['text']}}&nbsp;&nbsp;<span style="color: #8795A1">&#8594;</span>
            </a>
        </div>
    @endif
</div>
