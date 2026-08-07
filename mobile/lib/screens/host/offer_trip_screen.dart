import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../models/vehicle.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class OfferTripScreen extends StatefulWidget {
  const OfferTripScreen({super.key});

  @override
  State<OfferTripScreen> createState() => _OfferTripScreenState();
}

class _OfferTripScreenState extends State<OfferTripScreen> {
  final _formKey = GlobalKey<FormState>();
  final _originController = TextEditingController();
  final _destinationController = TextEditingController();
  final _notesController = TextEditingController();
  final _seatsController = TextEditingController(text: '1');
  final _priceController = TextEditingController();

  DateTime? _departureDate;
  TimeOfDay? _departureTime;
  Vehicle? _selectedVehicle;
  bool _instantBooking = true;
  bool _isLoading = false;
  bool _isLoadingVehicles = true;

  List<Vehicle> _vehicles = [];

  @override
  void initState() {
    super.initState();
    _loadVehicles();
  }

  @override
  void dispose() {
    _originController.dispose();
    _destinationController.dispose();
    _notesController.dispose();
    _seatsController.dispose();
    _priceController.dispose();
    super.dispose();
  }

  Future<void> _loadVehicles() async {
    setState(() => _isLoadingVehicles = true);
    final api = ApiClient();
    final response = await api.get('/vehicles');
    if (mounted) {
      setState(() {
        _isLoadingVehicles = false;
        if (response.success && response.data is Map) {
          final list = response.data['data'] as List? ?? [];
          _vehicles = list.map((v) => Vehicle.fromJson(v)).toList();
        }
      });
    }
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _departureDate ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (date != null && mounted) {
      setState(() => _departureDate = date);
      // Auto-open time picker
      if (_departureTime == null) {
        final time = await showTimePicker(
          context: context,
          initialTime: const TimeOfDay(hour: 7, minute: 0),
        );
        if (time != null && mounted) {
          setState(() => _departureTime = time);
        }
      }
    }
  }

  Future<void> _selectTime() async {
    final time = await showTimePicker(
      context: context,
      initialTime: _departureTime ?? const TimeOfDay(hour: 7, minute: 0),
    );
    if (time != null && mounted) {
      setState(() => _departureTime = time);
    }
  }

  Future<void> _publishTrip() async {
    if (!_formKey.currentState!.validate()) return;
    if (_departureDate == null || _departureTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select departure date and time')),
      );
      return;
    }
    if (_selectedVehicle == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a vehicle')),
      );
      return;
    }

    setState(() => _isLoading = true);

    final departure = DateTime(
      _departureDate!.year,
      _departureDate!.month,
      _departureDate!.day,
      _departureTime!.hour,
      _departureTime!.minute,
    );

    final body = {
      'origin': _originController.text.trim(),
      'destination': _destinationController.text.trim(),
      'departure_time': departure.toIso8601String(),
      'vehicle_id': _selectedVehicle!.id,
      'available_seats': int.tryParse(_seatsController.text) ?? 1,
      'price_per_seat': double.tryParse(_priceController.text) ?? 0,
      'booking_mode': _instantBooking ? 'instant' : 'request_approval',
      if (_notesController.text.trim().isNotEmpty)
        'notes': _notesController.text.trim(),
    };

    final api = ApiClient();
    final response = await api.post('/trips', body: body);

    if (mounted) {
      setState(() => _isLoading = false);
      if (response.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Trip published successfully!'),
            backgroundColor: AppTheme.success,
          ),
        );
        Navigator.pop(context, true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response.message ?? 'Failed to publish trip'),
            backgroundColor: AppTheme.error,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Offer a Trip'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(AppTheme.spacingMd),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Origin
              TextFormField(
                controller: _originController,
                decoration: const InputDecoration(
                  labelText: 'From City',
                  prefixIcon: Icon(Icons.location_on),
                  hintText: 'e.g. Mumbai',
                ),
                validator: (v) =>
                    v == null || v.trim().isEmpty ? 'Enter origin city' : null,
              ),
              const SizedBox(height: AppTheme.spacingMd),

              // Destination
              TextFormField(
                controller: _destinationController,
                decoration: const InputDecoration(
                  labelText: 'To City',
                  prefixIcon: Icon(Icons.location_on_outlined),
                  hintText: 'e.g. Pune',
                ),
                validator: (v) =>
                    v == null || v.trim().isEmpty ? 'Enter destination city' : null,
              ),
              const SizedBox(height: AppTheme.spacingMd),

              // Date & Time
              Row(
                children: [
                  Expanded(
                    child: _buildDateTimeChip(
                      icon: Icons.calendar_today,
                      label: _departureDate != null
                          ? DateFormat('MMM dd, yyyy').format(_departureDate!)
                          : 'Select Date',
                      onTap: _selectDate,
                    ),
                  ),
                  const SizedBox(width: AppTheme.spacingSm),
                  Expanded(
                    child: _buildDateTimeChip(
                      icon: Icons.access_time,
                      label: _departureTime != null
                          ? _departureTime!.format(context)
                          : 'Select Time',
                      onTap: _selectTime,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppTheme.spacingMd),

              // Vehicle Selector
              if (_isLoadingVehicles)
                const Center(child: CircularProgressIndicator())
              else if (_vehicles.isEmpty)
                Card(
                  child: ListTile(
                    leading: const Icon(Icons.directions_car),
                    title: const Text('No vehicles added'),
                    subtitle: const Text('Add a vehicle to offer trips'),
                    trailing: const Icon(Icons.add),
                    onTap: () {
                      // TODO: Navigate to add vehicle
                    },
                  ),
                )
              else
                DropdownButtonFormField<Vehicle>(
                  value: _selectedVehicle,
                  decoration: const InputDecoration(
                    labelText: 'Vehicle',
                    prefixIcon: Icon(Icons.directions_car),
                  ),
                  items: _vehicles.map((v) {
                    return DropdownMenuItem(
                      value: v,
                      child: Text('${v.displayName} (${v.number})'),
                    );
                  }).toList(),
                  onChanged: (v) => setState(() => _selectedVehicle = v),
                  validator: (v) => v == null ? 'Select a vehicle' : null,
                ),
              const SizedBox(height: AppTheme.spacingMd),

              // Seats & Price
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _seatsController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Seats',
                        prefixIcon: Icon(Icons.people),
                      ),
                      validator: (v) {
                        final n = int.tryParse(v ?? '');
                        if (n == null || n < 1) return 'Min 1 seat';
                        return null;
                      },
                    ),
                  ),
                  const SizedBox(width: AppTheme.spacingMd),
                  Expanded(
                    child: TextFormField(
                      controller: _priceController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Price/Seat (₹)',
                        prefixIcon: Icon(Icons.attach_money),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: AppTheme.spacingMd),

              // Booking Mode Toggle
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingMd),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Booking Mode',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      const SizedBox(height: AppTheme.spacingSm),
                      Row(
                        children: [
                          Expanded(
                            child: _BookingModeOption(
                              label: 'Instant',
                              subtitle: 'Auto-confirm',
                              selected: _instantBooking,
                              onTap: () =>
                                  setState(() => _instantBooking = true),
                            ),
                          ),
                          const SizedBox(width: AppTheme.spacingSm),
                          Expanded(
                            child: _BookingModeOption(
                              label: 'Request',
                              subtitle: 'You approve',
                              selected: !_instantBooking,
                              onTap: () =>
                                  setState(() => _instantBooking = false),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: AppTheme.spacingMd),

              // Notes
              TextFormField(
                controller: _notesController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Notes (optional)',
                  alignLabelWithHint: true,
                  hintText: 'Pickup points, preferences, etc.',
                ),
              ),
              const SizedBox(height: AppTheme.spacingLg),

              // Publish Button
              SizedBox(
                height: 52,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _publishTrip,
                  child: _isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text('Publish Trip'),
                ),
              ),
              const SizedBox(height: AppTheme.spacingMd),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDateTimeChip({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppTheme.radiusMd),
      child: Container(
        padding: const EdgeInsets.symmetric(
          horizontal: AppTheme.spacingMd,
          vertical: 14,
        ),
        decoration: BoxDecoration(
          color: AppTheme.surface,
          border: Border.all(color: AppTheme.border),
          borderRadius: BorderRadius.circular(AppTheme.radiusMd),
        ),
        child: Row(
          children: [
            Icon(icon, color: AppTheme.textSecondary, size: 20),
            const SizedBox(width: AppTheme.spacingSm),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  color: label.startsWith('Select')
                      ? AppTheme.textLight
                      : AppTheme.textPrimary,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BookingModeOption extends StatelessWidget {
  final String label;
  final String subtitle;
  final bool selected;
  final VoidCallback onTap;

  const _BookingModeOption({
    required this.label,
    required this.subtitle,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(AppTheme.spacingSm),
        decoration: BoxDecoration(
          color: selected
              ? AppTheme.primary.withValues(alpha: 0.1)
              : Colors.transparent,
          border: Border.all(
            color: selected ? AppTheme.primary : AppTheme.border,
            width: selected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(AppTheme.radiusSm),
        ),
        child: Column(
          children: [
            Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.w600,
                color: selected ? AppTheme.primary : AppTheme.textPrimary,
              ),
            ),
            Text(
              subtitle,
              style: TextStyle(
                fontSize: 11,
                color: selected ? AppTheme.primary : AppTheme.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
