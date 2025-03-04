<?php

namespace App\Filament\Resources\Pharmacy;

use App\Filament\Resources\Pharmacy\OrderResource\Pages;
use App\Filament\Resources\Pharmacy\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use App\Traits\SendMailTrait;
use Filament\Forms\Form;
use App\Notifications\OrderShippedNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log;

class OrderResource extends Resource
{
    use SendMailTrait; // استخدام الـ Trait

    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Orders cash';
    protected static ?string $navigationGroup = 'Pharmacy Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('status')
                    ->label('Order Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::where('payment_method', 'cash'))
            ->columns([
                TextColumn::make('id')->label('Order ID')->sortable(),
                TextColumn::make('name')->label('Customer Name')->searchable(),
                TextColumn::make('phone')->label('Phone')->searchable(),
                TextColumn::make('total_price')->label('Total Price')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')->label('Order Date')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->form(fn (Form $form) => self::form($form))
                    ->label('Update Status')
                    ->modalHeading('Update Order Status')
                    ->icon('heroicon-o-pencil')
           

                    ->after(function (Order $order) {
                    Log::info("Executing after() for Order #{$order->id}");

    // التحقق مما إذا تم تغيير الحالة إلى "shipped"
    $oldStatus = $order->getOriginal('status');
    Log::info("Old Status: {$oldStatus}, New Status: {$order->status}");

    if ($order->wasChanged('status') && $order->status === 'shipped') {
        Log::info("Order #{$order->id} status changed to 'shipped'.");

        // التحقق من وجود المستخدم
        if ($order->user) {
            Log::info("User found for order #{$order->id}: User ID = {$order->user->id}");

            // التحقق من وجود البريد الإلكتروني
            if ($order->user->email) {
                Log::info("Email found for user #{$order->user->id}: {$order->user->email}");

                try {
                    // إرسال الإشعار
                    Log::info("Sending notification to user #{$order->user->id}...");
                    $order->user->notify(new OrderShippedNotification($order));
                    Log::info("Notification sent successfully!");

                    // إرسال البريد الإلكتروني
                    $receiver_mail = $order->user->email;
                    $msg_title = 'Your Order Has Been Shipped!';
                    $msg_content = 'Your order #' . $order->id . ' has been shipped successfully.';

                    $mailer = new class {
                        use SendMailTrait;
                    };

                    $result = $mailer->sendEmail($receiver_mail, $msg_title, $msg_content);

                    if ($result['status'] !== 200) {
                        Log::error("Email failed to send for order #{$order->id}: " . $result['error']);
                    } else {
                        Log::info("Email sent successfully to: {$receiver_mail}");
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to send notification/email for order #{$order->id}: " . $e->getMessage());
                }

            } else {
                Log::error("No email found for user #{$order->user->id} in order #{$order->id}.");
            }
        } else {
            Log::error("No user found for order #{$order->id}.");
        }
    } else {
        Log::warning("Order #{$order->id} status not changed or not 'shipped'.");
    }
}),

            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}