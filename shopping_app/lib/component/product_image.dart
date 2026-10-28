import 'package:flutter/material.dart';

/// Displays a bundled asset or a network image with a graceful fallback.
class ProductImage extends StatelessWidget {
  const ProductImage({
    super.key,
    required this.path,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
    this.borderRadius,
  });

  final String path;
  final double? width;
  final double? height;
  final BoxFit fit;
  final BorderRadius? borderRadius;

  bool get _isNetwork => path.startsWith('http');

  @override
  Widget build(BuildContext context) {
    final image = _isNetwork
        ? Image.network(
            path,
            width: width,
            height: height,
            fit: fit,
            errorBuilder: _fallback,
          )
        : Image.asset(
            path,
            width: width,
            height: height,
            fit: fit,
            errorBuilder: _fallback,
          );

    if (borderRadius == null) return image;
    return ClipRRect(borderRadius: borderRadius!, child: image);
  }

  Widget _fallback(BuildContext context, Object error, StackTrace? stackTrace) {
    return Container(
      width: width,
      height: height,
      color: const Color(0xFFE7DFCB),
      alignment: Alignment.center,
      child: Icon(
        Icons.local_cafe_outlined,
        color: Theme.of(context).colorScheme.primary,
        size: 32,
      ),
    );
  }
}
