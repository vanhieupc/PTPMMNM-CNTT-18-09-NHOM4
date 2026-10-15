<?php
declare(strict_types=1);

final class RegistrationPolicy
{
    public function decide(int $capacity, int $registered, bool $isOpen, bool $hasWaitingList): string
    {
        // 1. Kiểm tra dữ liệu không hợp lệ hoặc số lượng đăng ký vượt quá sức chứa
        if ($capacity <= 0 || $registered < 0 || $registered > $capacity) {
            return 'rejected';
        }

        // 2. Nếu sự kiện đã đóng (isOpen = false), từ chối ngay cả khi còn chỗ
        if (!$isOpen) {
            return 'rejected';
        }

        // 3. Nếu sự kiện mở và còn chỗ (số người đăng ký nhỏ hơn sức chứa)
        if ($registered < $capacity) {
            return 'confirmed';
        }

        // 4. Nếu sự kiện mở và đã đầy chỗ (registered >= capacity)
        if ($hasWaitingList) {
            return 'waiting';
        }

        return 'rejected';
    }
}