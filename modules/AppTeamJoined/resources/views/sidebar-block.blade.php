@php
    $teams = UserInfo::getJoinedTeams();
    $currentTeamSecure = session('current_team_secure');
@endphp

@if($teams && count($teams))
<div class="menu-item mb-2">
    <label class="form-label mb-1">{{ __("Select Team") }}</label>
    <div class="input-group">
     	<select class="form-select actionChange" name="team_id" data-url="{{ route("app.teams.joined.open_team") }}">
	        <option value="0" {{ empty($currentTeamSecure) ? 'selected' : '' }}>
	            {{ __("Your Team") }}
	        </option>
	        @foreach($teams as $value)
	            <option value="{{ $value['id_secure'] }}"
	                {{ $currentTeamSecure == $value['id_secure'] ? 'selected' : '' }}>
	                {{ $value['name'] }}
	            </option>
	        @endforeach
	    </select>
 		<a href="{{ route("app.teams.joined") }}" class="btn btn-light btn-icon" data-bs-title="{{ __("Joined Teams") }}" data-bs-toggle="tooltip" data-bs-placement="top">
      		<i class="fa-light fa-people-group"></i>
 		</a>
    </div>
    
</div>
@endif