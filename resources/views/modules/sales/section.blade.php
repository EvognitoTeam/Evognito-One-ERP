@extends('layouts.workspace')

@section(
    'title',
    __('sales.sections.'.$section.'.title').' - Sales Force'
)

@section('content')

<section>
    <div
        class="text-xs
               font-bold
               uppercase
               tracking-[0.12em]
               text-teal-700"
    >
        Sales Force
    </div>

    <h1
        class="mt-2
               text-3xl
               font-semibold
               tracking-[-0.04em]
               text-[#252421]"
    >
        {{
            __('sales.sections.'.$section.'.title')
        }}
    </h1>

    <p
        class="mt-3
               max-w-3xl
               text-sm
               leading-6
               text-[#77746d]"
    >
        {{
            __('sales.sections.'.$section.'.description')
        }}
    </p>
</section>


<div class="mt-8">
    @include(
        'modules.sales.partials.navigation'
    )
</div>


<section
    class="mt-6
           rounded-2xl
           border
           border-black/[0.06]
           bg-white
           p-6"
>
    <div
        class="max-w-3xl"
    >
        <span
            class="inline-flex
                   rounded-full
                   bg-teal-50
                   px-3 py-1.5
                   text-xs
                   font-semibold
                   text-teal-700"
        >
            {{ __('sales.common.foundation_ready') }}
        </span>

        <h2
            class="mt-4
                   text-xl
                   font-semibold
                   tracking-tight
                   text-[#252421]"
        >
            {{
                __('sales.sections.'.$section.'.next_title')
            }}
        </h2>

        <p
            class="mt-3
                   text-sm
                   leading-6
                   text-[#77746d]"
        >
            {{
                __('sales.sections.'.$section.'.next_description')
            }}
        </p>
    </div>


    <div
        class="mt-6
               grid
               gap-3
               md:grid-cols-2
               xl:grid-cols-3"
    >
        @foreach(
            __('sales.sections.'.$section.'.features')
            as $feature
        )
            <div
                class="rounded-xl
                       border
                       border-black/[0.06]
                       bg-[#faf9f6]
                       p-4"
            >
                <div
                    class="text-sm
                           font-semibold
                           text-[#44413c]"
                >
                    {{ $feature }}
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection
