import 'package:flutter/material.dart';
import '../../models/rental_listing.dart';
import '../../services/rental_service.dart';

class RentalBookingScreen extends StatefulWidget {
  final RentalListing listing;
  const RentalBookingScreen({super.key, required this.listing});

  @override
  State<RentalBookingScreen> createState() => _RentalBookingScreenState();
}

class _RentalBookingScreenState extends State<RentalBookingScreen> {
  final _service = RentalService();
  final _messageController = TextEditingController();
  DateTime? _checkIn;
  DateTime? _checkOut;
  int _guestsCount = 1;
  bool _isSubmitting = false;

  int get _nights {
    if (_checkIn == null || _checkOut == null) return 0;
    return _checkOut!.difference(_checkIn!).inDays;
  }

  double get _subtotal => widget.listing.pricePerUnit * _nights;
  double get _serviceFee => _subtotal * 0.05;
  double get _total => _subtotal + widget.listing.cleaningFee + _serviceFee;

  @override
  void dispose() {
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _selectDate({required bool isCheckIn}) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isCheckIn ? DateTime.now() : DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        if (isCheckIn) {
          _checkIn = picked;
          if (_checkOut != null && _checkOut!.isBefore(_checkIn!)) {
            _checkOut = null;
          }
        } else {
          _checkOut = picked;
        }
      });
    }
  }

  Future<void> _submitBooking() async {
    if (_checkIn == null || _checkOut == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select check-in and check-out dates')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final _ = await _service.createBooking(widget.listing.id, {
        'check_in': _checkIn!.toIso8601String().split('T')[0],
        'check_out': _checkOut!.toIso8601String().split('T')[0],
        'guests_count': _guestsCount,
        'guest_message': _messageController.text.isNotEmpty ? _messageController.text : null,
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Booking created successfully!'), backgroundColor: Colors.green),
        );
        Navigator.pop(context);
        Navigator.pop(context); // Go back to listing list
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final listing = widget.listing;

    return Scaffold(
      appBar: AppBar(title: const Text('Book Listing')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Listing summary
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    if (listing.photos != null && listing.photos!.isNotEmpty)
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.network(
                          listing.photos!.first,
                          width: 60,
                          height: 60,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(
                            width: 60,
                            height: 60,
                            color: Colors.grey[200],
                            child: Center(child: Text(listing.typeIcon, style: const TextStyle(fontSize: 24))),
                          ),
                        ),
                      ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(listing.title, style: const TextStyle(fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis),
                          Text('${listing.city}, ${listing.state}', style: TextStyle(fontSize: 13, color: Colors.grey[600])),
                          Text(listing.formattedPrice, style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Check-in date
            const Text('Check-in Date', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            InkWell(
              onTap: () => _selectDate(isCheckIn: true),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey[300]!),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today, color: Colors.blue),
                    const SizedBox(width: 12),
                    Text(
                      _checkIn != null
                          ? '${_checkIn!.day}/${_checkIn!.month}/${_checkIn!.year}'
                          : 'Select date',
                      style: TextStyle(
                        fontSize: 16,
                        color: _checkIn != null ? Colors.black : Colors.grey,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Check-out date
            const Text('Check-out Date', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            InkWell(
              onTap: () => _selectDate(isCheckIn: false),
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey[300]!),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.calendar_today, color: Colors.orange),
                    const SizedBox(width: 12),
                    Text(
                      _checkOut != null
                          ? '${_checkOut!.day}/${_checkOut!.month}/${_checkOut!.year}'
                          : 'Select date',
                      style: TextStyle(
                        fontSize: 16,
                        color: _checkOut != null ? Colors.black : Colors.grey,
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Guests
            const Text('Guests', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Row(
              children: [
                IconButton(
                  onPressed: _guestsCount > 1 ? () => setState(() => _guestsCount--) : null,
                  icon: const Icon(Icons.remove_circle_outline),
                ),
                Text('$_guestsCount', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                IconButton(
                  onPressed: _guestsCount < 10 ? () => setState(() => _guestsCount++) : null,
                  icon: const Icon(Icons.add_circle_outline),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Message
            const Text('Message to Host (Optional)', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            TextField(
              controller: _messageController,
              maxLines: 3,
              decoration: InputDecoration(
                hintText: 'Any special requests or questions...',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 24),

            // Price breakdown
            if (_nights > 0) ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Price Breakdown', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      const Divider(),
                      _buildPriceRow('₹${listing.pricePerUnit.toStringAsFixed(0)} × $_nights nights', '₹${_subtotal.toStringAsFixed(0)}'),
                      if (listing.cleaningFee > 0)
                        _buildPriceRow('Cleaning Fee', '₹${listing.cleaningFee.toStringAsFixed(0)}'),
                      _buildPriceRow('Service Fee (5%)', '₹${_serviceFee.toStringAsFixed(0)}'),
                      const Divider(),
                      _buildPriceRow('Total', '₹${_total.toStringAsFixed(0)}', isBold: true),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // Submit button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (_checkIn != null && _checkOut != null && !_isSubmitting)
                    ? _submitBooking
                    : null,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _isSubmitting
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Text('Confirm Booking', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildPriceRow(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(
            fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            fontSize: isBold ? 16 : 14,
          )),
          Text(value, style: TextStyle(
            fontWeight: isBold ? FontWeight.bold : FontWeight.w600,
            fontSize: isBold ? 18 : 14,
            color: isBold ? Colors.green : null,
          )),
        ],
      ),
    );
  }
}
