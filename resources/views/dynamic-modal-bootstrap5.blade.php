<button class="btn btn-primary" onclick="openUserModal({{ $user->id }})">View User</button>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Dynamic user data will be displayed here -->
                <p><strong>Name: </strong><span id="userName"></span></p>
                <p><strong>Email: </strong><span id="userEmail"></span></p>
                <p><strong>Phone: </strong><span id="userPhone"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to open modal and fetch user data
    function openUserModal(userId) {
        axios.get(`/users/${userId}`)
            .then(function (response) {
                // Populate modal with user data
                document.getElementById('userName').innerText = response.data.name;
                document.getElementById('userEmail').innerText = response.data.email;
                document.getElementById('userPhone').innerText = response.data.phone;

                // Show the modal
                var userModal = new bootstrap.Modal(document.getElementById('userModal'));
                userModal.show();
            })
            .catch(function (error) {
                console.log(error);
            });
    }
</script>

public function getUser($id)
{
    // Fetch user from database
    $user = User::findOrFail($id);

    // Return user data as JSON
    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
    ]);
}


@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Users</h1>
        @foreach($users as $user)
            <p>{{ $user->name }} <button class="btn btn-primary" onclick="openUserModal({{ $user->id }})">View</button></p>
        @endforeach
    </div>

    <!-- Include Modal -->
    @include('modals.user-modal')

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function openUserModal(userId) {
            axios.get(`/users/${userId}`)
                .then(function (response) {
                    document.getElementById('userName').innerText = response.data.name;
                    document.getElementById('userEmail').innerText = response.data.email;
                    document.getElementById('userPhone').innerText = response.data.phone;

                    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
                    userModal.show();
                })
                .catch(function (error) {
                    console.log(error);
                });
        }
    </script>
@endsection
