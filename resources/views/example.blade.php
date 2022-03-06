@if (true)

<form action="{{ route('form-create') }}" method="post">
    @csrf
    @method('post')

    <h1>Create a store</h1>

    <input type="text" name="name" placeholder="Store name">

    <br/><br/>

    <textarea name="description" placeholder="Store description"></textarea>

    <br/><br/>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <button type="submit">Create Store</button>
</form>

@endif
