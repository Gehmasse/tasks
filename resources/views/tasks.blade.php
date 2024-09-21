<x-app>

    <h1>{{ $title }}</h1>

    <livewire:task-list :filter="$filter" :params="$params ?? []"/>

</x-app>
