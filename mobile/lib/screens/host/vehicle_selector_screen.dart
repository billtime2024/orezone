import 'package:flutter/material.dart';
import '../../models/vehicle.dart';
import '../../services/api_client.dart';
import '../../theme/app_theme.dart';

class VehicleSelectorScreen extends StatefulWidget {
  final Vehicle? selectedVehicle;

  const VehicleSelectorScreen({super.key, this.selectedVehicle});

  @override
  State<VehicleSelectorScreen> createState() => _VehicleSelectorScreenState();
}

class _VehicleSelectorScreenState extends State<VehicleSelectorScreen> {
  bool _isLoading = true;
  List<Vehicle> _vehicles = [];
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadVehicles();
  }

  Future<void> _loadVehicles() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    final api = ApiClient();
    final response = await api.get('/vehicles');
    if (mounted) {
      setState(() {
        _isLoading = false;
        if (response.success && response.data is Map) {
          final list = response.data['data'] as List? ?? [];
          _vehicles = list.map((v) => Vehicle.fromJson(v)).toList();
        } else {
          _error = response.message ?? 'Failed to load vehicles';
        }
      });
    }
  }

  IconData _vehicleIcon(String type) {
    switch (type.toLowerCase()) {
      case 'bike':
        return Icons.two_wheeler;
      case 'auto':
        return Icons.local_taxi;
      case 'car':
      default:
        return Icons.directions_car;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Select Vehicle')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          // TODO: Navigate to add vehicle form
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Add Vehicle form coming soon')),
          );
        },
        icon: const Icon(Icons.add),
        label: const Text('Add Vehicle'),
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(_error!, style: const TextStyle(color: AppTheme.error)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadVehicles,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : _vehicles.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.directions_car_outlined,
                            size: 64,
                            color: AppTheme.textLight,
                          ),
                          const SizedBox(height: 16),
                          const Text(
                            'No vehicles added yet',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          const Text(
                            'Add your vehicle to start offering trips',
                            style: TextStyle(color: AppTheme.textSecondary),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _loadVehicles,
                      child: ListView.separated(
                        padding: const EdgeInsets.all(AppTheme.spacingMd),
                        itemCount: _vehicles.length,
                        separatorBuilder: (_, __) =>
                            const SizedBox(height: AppTheme.spacingSm),
                        itemBuilder: (context, index) {
                          final vehicle = _vehicles[index];
                          final isSelected =
                              widget.selectedVehicle?.id == vehicle.id;
                          return Card(
                            color: isSelected
                                ? AppTheme.primary.withValues(alpha: 0.08)
                                : null,
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: isSelected
                                    ? AppTheme.primary
                                    : AppTheme.primary.withValues(alpha: 0.1),
                                child: Icon(
                                  _vehicleIcon(vehicle.type),
                                  color: isSelected
                                      ? Colors.white
                                      : AppTheme.primary,
                                ),
                              ),
                              title: Text(vehicle.displayName),
                              subtitle: Text(
                                '${vehicle.number} • ${vehicle.seats} seats'
                                '${vehicle.color != null ? ' • ${vehicle.color}' : ''}',
                              ),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  if (vehicle.isVerified)
                                    const Icon(
                                      Icons.verified,
                                      color: AppTheme.success,
                                      size: 20,
                                    ),
                                  if (isSelected)
                                    const Icon(
                                      Icons.check_circle,
                                      color: AppTheme.primary,
                                    ),
                                ],
                              ),
                              onTap: () => Navigator.pop(context, vehicle),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
