<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NetPack Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white text-slate-800 py-3.5 shadow-xs border-b border-slate-200">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <x-logo variant="dark" size="sm" :href="route('home')" />
                <a href="{{ route('login') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900 transition flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 border border-teal-200 hover:bg-teal-100">
                    <i class="fas fa-sign-in-alt"></i> Back to Login
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Service Type Selection -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-slate-200/80">
                    <h2 class="text-2xl font-bold text-gray-800 text-center mb-1">Create Your Client / Business Account</h2>
                    <p class="text-gray-500 text-xs text-center mb-6">Join COURIER with NETPACK &bull; Nepal's Premier Multi-Service Logistics Platform</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                        <a href="{{ route('register') }}?type=client" 
                           class="p-3 rounded-lg text-center transition {{ (isset($userType) && in_array($userType, ['client', 'customer'])) ? 'bg-teal-600 text-white shadow-sm font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                            <i class="fas fa-user-shield text-xl block mb-1"></i>
                            <span class="text-sm">Client</span>
                        </a>
                        <a href="{{ route('register') }}?type=seller" 
                           class="p-3 rounded-lg text-center transition {{ isset($userType) && $userType === 'seller' ? 'bg-teal-600 text-white shadow-sm font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                            <i class="fas fa-store text-xl block mb-1"></i>
                            <span class="text-sm">Seller</span>
                        </a>
                        <a href="{{ route('register') }}?type=rider" 
                           class="p-3 rounded-lg text-center transition {{ isset($userType) && $userType === 'rider' ? 'bg-teal-600 text-white shadow-sm font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                            <i class="fas fa-motorcycle text-xl block mb-1"></i>
                            <span class="text-sm">Rider</span>
                        </a>
                        <a href="{{ route('register') }}?type=partner" 
                           class="p-3 rounded-lg text-center transition {{ isset($userType) && $userType === 'partner' ? 'bg-teal-600 text-white shadow-sm font-semibold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                            <i class="fas fa-handshake text-xl block mb-1"></i>
                            <span class="text-sm">Partner</span>
                        </a>
                    </div>

                    <!-- Current Selection Info -->
                    <div class="text-center text-xs text-gray-500">
                        <i class="fas fa-info-circle text-teal-500"></i>
                        You are registering as: <strong class="text-teal-600 font-bold uppercase">{{ (in_array($userType ?? 'client', ['client', 'customer'])) ? 'Client' : ucfirst($userType) }}</strong>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200/80">
                    <form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="user_type" value="{{ in_array($userType ?? 'client', ['client', 'customer']) ? 'client' : $userType }}">

                        @if($errors->any())
                            <div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Personal Information -->
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Personal Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone Number *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Date of Birth *</label>
                                <input type="date" name="dob" value="{{ old('dob') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('dob') border-red-500 @enderror">
                                @error('dob')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Gender *</label>
                                <select name="gender" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('gender') border-red-500 @enderror">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nationality *</label>
                                <select name="nationality" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('nationality') border-red-500 @enderror">
                                    <option value="">Select Nationality</option>
                                    <option value="Nepali" {{ old('nationality') === 'Nepali' ? 'selected' : '' }}>Nepali</option>
                                    <option value="Indian" {{ old('nationality') === 'Indian' ? 'selected' : '' }}>Indian</option>
                                    <option value="Chinese" {{ old('nationality') === 'Chinese' ? 'selected' : '' }}>Chinese</option>
                                    <option value="American" {{ old('nationality') === 'American' ? 'selected' : '' }}>American</option>
                                    <option value="British" {{ old('nationality') === 'British' ? 'selected' : '' }}>British</option>
                                    <option value="Other" {{ old('nationality') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('nationality')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Information -->
                        <h3 class="text-lg font-semibold text-gray-700 mt-4 mb-3 border-b pb-2">Address Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-1">Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">City</label>
                                <input type="text" name="city" value="{{ old('city') }}" 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div class="md:col-span-2">
                                <x-nepal-territory-picker 
                                    provinceName="province"
                                    districtName="district"
                                    :selectedProvince="old('province')"
                                    :selectedDistrict="old('district')"
                                    provinceLabel="Province / Sector"
                                    districtLabel="District"
                                    helperText="Select province to display strictly its affiliated 77 districts with quick search."
                                    :required="false"
                                />
                            </div>
                        </div>

                        <!-- Business Information (for Sellers & Partners) -->
                        @if(isset($userType) && in_array($userType, ['seller', 'partner']))
                        <h3 class="text-lg font-semibold text-gray-700 mt-4 mb-3 border-b pb-2">Business Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Business Name *</label>
                                <input type="text" name="business_name" value="{{ old('business_name') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('business_name') border-red-500 @enderror">
                                @error('business_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Business Address *</label>
                                <input type="text" name="business_address" value="{{ old('business_address') }}" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('business_address') border-red-500 @enderror">
                                @error('business_address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">PAN Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number') }}" 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        @endif

                        <!-- Rider Information & KYC Verification -->
                        @if(isset($userType) && $userType === 'rider')
                        <div class="mt-6 p-4 rounded-xl bg-teal-50/80 border border-teal-200">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-motorcycle text-teal-600 text-lg mt-0.5"></i>
                                <div>
                                    <h4 class="font-bold text-sm text-teal-900">Direct Rider Registration & Verification</h4>
                                    <p class="text-xs text-teal-700 mt-0.5 leading-relaxed">
                                        Any rider can register directly as an independent delivery service provider. Affiliation with Pathao, inDrive, or other apps is informational only and does not restrict your eligibility.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-b pb-2">Vehicle & License Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Vehicle Type *</label>
                                <select name="vehicle_type" required class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('vehicle_type') border-red-500 @enderror">
                                    <option value="">Select Vehicle</option>
                                    <option value="motorcycle" {{ old('vehicle_type', 'motorcycle') === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                    <option value="scooter" {{ old('vehicle_type') === 'scooter' ? 'selected' : '' }}>Scooter</option>
                                    <option value="bicycle" {{ old('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                                    <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Car</option>
                                    <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Van</option>
                                </select>
                                @error('vehicle_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Vehicle Registration / Plate No. *</label>
                                <input type="text" name="vehicle_registration_number" value="{{ old('vehicle_registration_number') }}" required placeholder="e.g. BA 99 PA 1234"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('vehicle_registration_number') border-red-500 @enderror">
                                @error('vehicle_registration_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Driving License Number *</label>
                                <input type="text" name="license_number" value="{{ old('license_number') }}" required placeholder="e.g. 01-06-00012345"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('license_number') border-red-500 @enderror">
                                @error('license_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">License Expiry Date</label>
                                <input type="date" name="license_expiry_date" value="{{ old('license_expiry_date') }}"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Citizenship Number</label>
                                <input type="text" name="citizenship_number" value="{{ old('citizenship_number') }}" placeholder="e.g. 27-01-78-12345"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Emergency Contact Phone & Name</label>
                                <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="e.g. 98XXXXXXXX (Father/Spouse)"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Municipality / Rural Municipality</label>
                                <input type="text" name="municipality" value="{{ old('municipality') }}" placeholder="e.g. Kathmandu Metropolitan City"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Ward Number</label>
                                <input type="text" name="ward" value="{{ old('ward') }}" placeholder="e.g. 10"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-b pb-2">Informational Platform Affiliation (Optional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Also Work With Another Platform?</label>
                                <select name="affiliation" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                    <option value="none" {{ old('affiliation') === 'none' ? 'selected' : '' }}>None (Full-Time Independent / NETPACK)</option>
                                    <option value="pathao" {{ old('affiliation') === 'pathao' ? 'selected' : '' }}>Pathao</option>
                                    <option value="indrive" {{ old('affiliation') === 'indrive' ? 'selected' : '' }}>inDrive</option>
                                    <option value="parcel" {{ old('affiliation') === 'parcel' ? 'selected' : '' }}>Parcel</option>
                                    <option value="courier_co" {{ old('affiliation') === 'courier_co' ? 'selected' : '' }}>Other Courier Company</option>
                                    <option value="freelance" {{ old('affiliation') === 'freelance' ? 'selected' : '' }}>Independent Freelancer</option>
                                    <option value="other" {{ old('affiliation') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <p class="text-[11px] text-gray-500 mt-1">Informational only — helps us understand your delivery experience.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Affiliation Reference / ID (Optional)</label>
                                <input type="text" name="affiliation_reference_id" value="{{ old('affiliation_reference_id') }}" placeholder="e.g. Pathao / inDrive ID"
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-3 border-b pb-2">Identity & Vehicle KYC Uploads (Optional during signup)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Driving License Photo</label>
                                <input type="file" name="driving_license_doc" accept="image/*,.pdf" class="w-full text-xs text-slate-600 border rounded-lg p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Vehicle Bluebook / Registration</label>
                                <input type="file" name="vehicle_registration_doc" accept="image/*,.pdf" class="w-full text-xs text-slate-600 border rounded-lg p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Citizenship Front</label>
                                <input type="file" name="citizenship_front" accept="image/*,.pdf" class="w-full text-xs text-slate-600 border rounded-lg p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Citizenship Back</label>
                                <input type="file" name="citizenship_back" accept="image/*,.pdf" class="w-full text-xs text-slate-600 border rounded-lg p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Rider Selfie / Profile Photo</label>
                                <input type="file" name="selfie_photo" accept="image/*" class="w-full text-xs text-slate-600 border rounded-lg p-2 bg-slate-50 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-teal-600 file:text-white">
                            </div>
                        </div>
                        @endif

                        <!-- Security -->
                        <h3 class="text-lg font-semibold text-gray-700 mt-4 mb-3 border-b pb-2">Security</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Password *</label>
                                <input type="password" name="password" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 @error('password') border-red-500 @enderror">
                                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Confirm Password *</label>
                                <input type="password" name="password_confirmation" required 
                                       class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="mt-4">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="terms" required 
                                       class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="text-sm text-gray-600">
                                    I agree to the 
                                    <a href="#" class="text-teal-600 hover:underline">Terms of Service</a> 
                                    and 
                                    <a href="#" class="text-teal-600 hover:underline">Privacy Policy</a>
                                </span>
                            </label>
                            @error('terms')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="mt-6 flex gap-3 pt-4 border-t">
                            <button type="submit" class="flex-1 bg-teal-600 text-white px-6 py-3 rounded-lg hover:bg-teal-700 transition font-semibold">
                                <i class="fas fa-user-plus mr-2"></i> Register
                            </button>
                            <a href="{{ route('login') }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4 mt-8">
            <div class="container mx-auto px-4 text-center text-sm">
                <p>&copy; {{ date('Y') }} NetPack Logistics. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>