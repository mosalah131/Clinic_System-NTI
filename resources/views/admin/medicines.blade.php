@extends('layouts.app')

@section('title', 'Medicines')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Medicines</h3>
            <p class="text-muted mb-0">The catalogue doctors choose from when writing a prescription</p>
        </div>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
            <i class="bi bi-plus-lg"></i> Add Medicine
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.medicines.index') }}" class="row g-3">

                <div class="col-md-5">
                    <label class="form-label">Search Medicine</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control" placeholder="Search by medicine name...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active"   @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-secondary w-100">Reset</a>
                </div>

                @if ($trashed)<input type="hidden" name="trashed" value="1">@endif

            </form>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.medicines.index') }}"
           class="btn btn-sm {{ $trashed ? 'btn-outline-secondary' : 'btn-primary' }}">Active</a>
        <a href="{{ route('admin.medicines.index', ['trashed' => 1]) }}"
           class="btn btn-sm {{ $trashed ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-trash"></i> Deleted
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($medicines as $index => $medicine)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <strong>{{ $medicine->name }}</strong>
                                    @if ($medicine->description)
                                        <small class="d-block text-muted">{{ Str::limit($medicine->description, 60) }}</small>
                                    @endif
                                </td>

                                <td>{{ $medicine->category ?: '-' }}</td>
                                <td>{{ $medicine->quantity }}</td>
                                <td>{{ number_format($medicine->price, 2) }}</td>

                                <td>
                                    <span class="badge {{ $medicine->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ucfirst($medicine->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($trashed)
                                        <form method="POST" action="{{ route('admin.medicines.restore', $medicine->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @else
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editMedicineModal"
                                                    data-fill-modal="editMedicineModal"
                                                    data-id="{{ $medicine->id }}"
                                                    data-name="{{ $medicine->name }}"
                                                    data-description="{{ $medicine->description }}"
                                                    data-category="{{ $medicine->category }}"
                                                    data-price="{{ $medicine->price }}"
                                                    data-quantity="{{ $medicine->quantity }}"
                                                    data-status="{{ $medicine->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form method="POST" action="{{ route('admin.medicines.destroy', $medicine) }}"
                                                  data-confirm="Delete {{ $medicine->name }}? Old prescriptions keep working and you can restore it.">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-capsule"></i>
                                        {{ $trashed ? 'There are no deleted medicines.' : 'No medicines match your search.' }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= Add medicine ================= --}}
    <div class="modal fade" id="addMedicineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.medicines.store') }}">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Add Medicine</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Medicine Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Panadol" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Painkiller">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" min="0" name="price" class="form-control" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="0" name="quantity" class="form-control" value="0" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="2" class="form-control"
                                          placeholder="e.g. 500 mg tablets"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Medicine</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ================= Edit medicine ================= --}}
    <div class="modal fade" id="editMedicineModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST"
                      data-action-template="{{ route('admin.medicines.update', '__ID__') }}"
                      action="{{ route('admin.medicines.update', '__ID__') }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Medicine</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Medicine Name</label>
                                <input type="text" name="name" data-field="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" data-field="category" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" data-field="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" min="0" name="price" data-field="price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="number" min="0" name="quantity" data-field="quantity" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" data-field="description" rows="2" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
