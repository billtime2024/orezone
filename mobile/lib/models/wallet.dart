class Wallet {
  final int id;
  final int userId;
  final double balance;
  final double totalEarned;
  final double totalSpent;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  Wallet({
    required this.id,
    required this.userId,
    required this.balance,
    this.totalEarned = 0.0,
    this.totalSpent = 0.0,
    required this.createdAt,
    required this.updatedAt,
  });
  
  factory Wallet.fromJson(Map<String, dynamic> json) {
    return Wallet(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      balance: (json['balance'] as num).toDouble(),
      totalEarned: (json['total_earned'] as num?)?.toDouble() ?? 0.0,
      totalSpent: (json['total_spent'] as num?)?.toDouble() ?? 0.0,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'balance': balance,
      'total_earned': totalEarned,
      'total_spent': totalSpent,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  String get formattedBalance => '₹${balance.toStringAsFixed(2)}';
  String get formattedEarned => '₹${totalEarned.toStringAsFixed(2)}';
  String get formattedSpent => '₹${totalSpent.toStringAsFixed(2)}';
}
