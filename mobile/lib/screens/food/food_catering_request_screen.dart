import 'package:flutter/material.dart';
import '../../services/food_service.dart';
import '../../theme/app_theme.dart';

class FoodCateringRequestScreen extends StatefulWidget {
  const FoodCateringRequestScreen({super.key});

  @override
  State<FoodCateringRequestScreen> createState() =>
      _FoodCateringRequestScreenState();
}

class _FoodCateringRequestScreenState
    extends State<FoodCateringRequestScreen> {
  final _service = FoodService();
  final _formKey = GlobalKey<FormState>();

  String _eventType = 'party';
  final _eventNameController = TextEditingController();
  final _venueController = TextEditingController();
  final _guestController = TextEditingController();
  final _budgetMinController = TextEditingController();
  final _budgetMaxController = TextEditingController();
  final _specialRequestsController = TextEditingController();

  DateTime? _selectedDate;
  TimeOfDay? _selectedTime;

  final _eventTypes = const [
    ('Wedding', 'wedding', '💒'),
    ('Birthday', 'birthday', '🎂'),
    ('Corporate', 'corporate', '💼'),
    ('Party', 'party', '🎉'),
  ];

  bool _isSubmitting = false;

  @override
  void dispose() {
    _eventNameController.dispose();
    _venueController.dispose();
    _guestController.dispose();
    _budgetMinController.dispose();
    _budgetMaxController.dispose();
    _specialRequestsController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now().add(const Duration(days: 7)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _selectTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _selectedTime ?? const TimeOfDay(hour: 12, minute: 0),
    );
    if (picked != null) {
      setState(() => _selectedTime = picked);
    }
  }

  Future<void> _submitRequest() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);
    try {
      final data = {
        'event_type': _eventType,
        'event_name': _eventNameController.text.isNotEmpty
            ? _eventNameController.text
            : null,
        'event_date': _selectedDate != null
            ? '${_selectedDate!.year}-${_selectedDate!.month.toString().padLeft(2, '0')}-${_selectedDate!.day.toString().padLeft(2, '0')}'
            : null,
        'event_time': _selectedTime != null
            ? '${_selectedTime!.hour.toString().padLeft(2, '0')}:${_selectedTime!.minute.toString().padLeft(2, '0')}'
            : null,
        'venue_address': _venueController.text.isNotEmpty
            ? _venueController.text
            : null,
        'guest_count': int.tryParse(_guestController.text),
        'budget_min': double.tryParse(_budgetMinController.text),
        'budget_max': double.tryParse(_budgetMaxController.text),
        'special_requests': _specialRequestsController.text.isNotEmpty
            ? _specialRequestsController.text
            : null,
      };

      final result = await _service.createCateringRequest(data);
      if (mounted) {
        setState(() => _isSubmitting = false);
        if (result != null) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Catering request submitted successfully!'),
              backgroundColor: AppTheme.success,
            ),
          );
          Navigator.pop(context);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Failed to submit request'),
              backgroundColor: AppTheme.error,
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmitting = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: AppTheme.error),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Catering Request'),
        elevation: 0,
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(AppTheme.spacingMd),
          children: [
            // Event type selector
            const Text('Event Type',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
            const SizedBox(height: AppTheme.spacingSm),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: _eventTypes.map((type) {
                final isSelected = _eventType == type.$2;
                return GestureDetector(
                  onTap: () => setState(() => _eventType = type.$2),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 12),
                    decoration: BoxDecoration(
                      color: isSelected
                          ? AppTheme.primary.withValues(alpha: 0.15)
                          : AppTheme.surface,
                      borderRadius:
                          BorderRadius.circular(AppTheme.radiusMd),
                      border: Border.all(
                        color: isSelected
                            ? AppTheme.primary
                            : AppTheme.border,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Text('${type.$3} ${type.$1}',
                        style: TextStyle(
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.normal,
                            color: isSelected
                                ? AppTheme.primary
                                : AppTheme.textPrimary)),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: AppTheme.spacingLg),

            // Event name
            TextFormField(
              controller: _eventNameController,
              decoration: const InputDecoration(
                labelText: 'Event Name (optional)',
                hintText: 'e.g., Sarah\'s Birthday',
              ),
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Date & Time
            Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: _selectDate,
                    child: InputDecorator(
                      decoration: const InputDecoration(
                        labelText: 'Event Date',
                        suffixIcon: Icon(Icons.calendar_today),
                      ),
                      child: Text(
                        _selectedDate != null
                            ? '${_selectedDate!.day}/${_selectedDate!.month}/${_selectedDate!.year}'
                            : 'Select date',
                        style: TextStyle(
                          color: _selectedDate != null
                              ? AppTheme.textPrimary
                              : AppTheme.textLight,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: AppTheme.spacingSm),
                Expanded(
                  child: GestureDetector(
                    onTap: _selectTime,
                    child: InputDecorator(
                      decoration: const InputDecoration(
                        labelText: 'Event Time',
                        suffixIcon: Icon(Icons.access_time),
                      ),
                      child: Text(
                        _selectedTime != null
                            ? _selectedTime!.format(context)
                            : 'Select time',
                        style: TextStyle(
                          color: _selectedTime != null
                              ? AppTheme.textPrimary
                              : AppTheme.textLight,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Guest count
            TextFormField(
              controller: _guestController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Guest Count',
                hintText: 'Number of guests',
                prefixIcon: Icon(Icons.people),
              ),
              validator: (val) {
                if (val == null || val.isEmpty) return 'Required';
                final n = int.tryParse(val);
                if (n == null || n <= 0) return 'Enter valid number';
                return null;
              },
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Budget range
            const Text('Budget Range (₹)',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
            const SizedBox(height: 4),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _budgetMinController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      hintText: 'Min',
                      prefixText: '₹ ',
                    ),
                  ),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 8),
                  child: Text('to'),
                ),
                Expanded(
                  child: TextFormField(
                    controller: _budgetMaxController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      hintText: 'Max',
                      prefixText: '₹ ',
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Venue address
            TextFormField(
              controller: _venueController,
              decoration: const InputDecoration(
                labelText: 'Venue Address',
                hintText: 'Enter full address',
                prefixIcon: Icon(Icons.location_on),
              ),
              maxLines: 2,
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Dietary preferences
            const Text('Dietary Preferences',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w500)),
            const SizedBox(height: 4),
            const Text('All food is Pure Veg. Additional preferences:',
                style: TextStyle(
                    fontSize: 12, color: AppTheme.textSecondary)),
            const SizedBox(height: 4),
            Wrap(
              spacing: 8,
              runSpacing: 4,
              children: const [
                Chip(label: Text(' Jain')),
                Chip(label: Text(' Vegan')),
                Chip(label: Text(' Gluten-Free')),
                Chip(label: Text(' Nut-Free')),
              ],
            ),
            const SizedBox(height: AppTheme.spacingMd),

            // Special requests
            TextFormField(
              controller: _specialRequestsController,
              decoration: const InputDecoration(
                labelText: 'Special Requests',
                hintText: 'Any specific requirements, menu preferences, etc.',
              ),
              maxLines: 3,
            ),
            const SizedBox(height: AppTheme.spacingLg),

            // Submit button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submitRequest,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: AppTheme.primary,
                  shape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(AppTheme.radiusMd)),
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2),
                      )
                    : const Text(
                        'Submit Request',
                        style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.white),
                      ),
              ),
            ),
            const SizedBox(height: AppTheme.spacingMd),
          ],
        ),
      ),
    );
  }
}
