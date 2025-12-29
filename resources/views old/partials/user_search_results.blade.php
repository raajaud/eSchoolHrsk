@if($users->count())
    <ul style="list-style:none; padding:0; margin:0;">
        @foreach($users as $user)
            @if($user->student)
            <li
                onclick="window.location='{{ route('fees.compulsory.index', [1, $user->student?->guardian_id]) }}'"
                style="padding:8px 10px; cursor:pointer; border-bottom:1px solid #eee;"
                onmouseover="this.style.background='#f0f0f0';"
                onmouseout="this.style.background='transparent';"
            >
                {{ $user->full_name }}
            </li>
            @endif
        @endforeach
    </ul>
@else
    <p style="margin:0; padding:8px 10px;">No users found.</p>
@endif
