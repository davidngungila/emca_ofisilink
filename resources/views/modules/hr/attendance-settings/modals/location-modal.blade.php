<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="locationForm" onsubmit="submitLocation(event)">
                <div class="modal-header">
                    <h5 class="modal-title" id="locationModalTitle">Add Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="locationId" name="id">
                    
                    <!-- Basic Info -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-info-circle"></i> Basic Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Location Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="locName" placeholder="e.g. Head Office" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" id="locCode" placeholder="e.g. HQ-001" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="locDescription" rows="2" placeholder="Brief description of this location"></textarea>
                        </div>
                    </div>

                    <!-- Address -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-map-pin"></i> Address & Contact</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="locAddress" placeholder="Street Address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" id="locCity">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State/Province</label>
                            <input type="text" class="form-control" name="state" id="locState">
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" id="locCountry">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" name="postal_code" id="locPostalCode">
                        </div>
                    </div>

                    <!-- Geofencing -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="bx bx-target-lock"></i> Geofencing & Coordinates</h6>
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="getCurrentLocation()">
                            <i class="bx bx-current-location"></i> Get Current Position
                        </button>
                    </div>
                    <div class="row g-3 mb-4">
                         <div class="col-md-4">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" name="latitude" id="locLatitude" placeholder="e.g. -6.7924">
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" name="longitude" id="locLongitude" placeholder="e.g. 39.2083">
                        </div>
                         <div class="col-md-4">
                            <label class="form-label">Radius (meters)</label>
                            <input type="number" class="form-control" name="radius_meters" id="locRadius" value="100">
                             <div class="form-text">Allowed radius for clock-in</div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-cog"></i> Configuration</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="locIsActive" checked>
                                <label class="form-check-label" for="locIsActive">Active Location</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="require_gps" id="locRequireGps">
                                <label class="form-check-label" for="locRequireGps">Require GPS</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch pt-2">
                                <input class="form-check-input" type="checkbox" name="allow_remote" id="locAllowRemote">
                                <label class="form-check-label" for="locAllowRemote">Allow Remote</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveLocationBtn">
                        <i class="bx bx-save"></i> Save Location
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
