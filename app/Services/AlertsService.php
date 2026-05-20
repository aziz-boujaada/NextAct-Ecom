<?php


namespace App\Services;

use App\Mail\StockAlertMail;
use App\Models\Alert;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;

class AlertsService
{
    public function getAdminsIDs()
    {
        return User::where('role', 'admin')->pluck('id');
    }

    public function stockAlert($product)
    {
        if ($product->stock > $product->alert_stock) {
            return;
        }

        $active = Alert::where('product_id', $product->id)
            ->where('status', 'active')
            ->exists();
        $alert = null;
        if ($product->stock <= $product->alert_stock) {

            if (!$active) {

                $alert = Alert::create([
                    'product_id' => $product->id,
                    'type' => $product->stock === 0 ? 'out_stock' : 'low_stock',
                    'stock' => $product->stock,
                    'alert_stock' => $product->alert_stock,
                    'status' => 'active'
                ]);
            }

            $this->sendEmailToAdmins($product);
            if ($alert) {
                $alert->users()->attach(
                    $this->getAdminsIDs()->mapWithKeys(fn($id) => [
                        $id => ['is_read' => false]
                    ])->toArray()
                );
            }




            return $alert;
        }
    }
    public function updateAlertStatus(Product $product)
    {
        if ($product->stock > $product->alert_stock) {
            Alert::where('product_id', $product->id)
                ->where('status', 'active')
                ->update(['status' => 'resolved']);
        }
    }
    private function sendEmailToAdmins($product)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new StockAlertMail($product, $admin));
        }
    }

    /**
     *handle method tha contain update staus of ALERT in differnt case:
     *(purchase craete /update , product create/update , Refund create)
     * and stock alert method that create alert when product hase low stock 
     **/

    public function handle($product)
    {

        $product = $product->fresh();

        $this->stockAlert($product);
        $this->updateAlertStatus($product);
    }
}
