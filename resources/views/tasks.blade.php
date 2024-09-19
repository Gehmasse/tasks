<x-app>

    <h1>{{ $title }}</h1>

    <livewire:task-list :method="$tasks" :params="$params ?? []"/>

</x-app>
