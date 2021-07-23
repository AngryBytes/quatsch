<?php
namespace Quatsch;

final class QuatschTest extends \PHPUnit\Framework\TestCase
{
    /** @small */
    public function testInt(): void
    {
        $fixture = [4, 2, 5, 4, 3, 1, 5, 5, 5, 4];
        $this->sample10($fixture, function (Quatsch $gen) {
            return $gen->int(1, 5);
        });
    }

    /** @small */
    public function testBool(): void
    {
        $fixture = [
            true, false, true, true, true,
            false, true, true, true, true,
        ];
        $this->sample10($fixture, function (Quatsch $gen) {
            return $gen->bool();
        });
    }

    /** @small */
    public function testDateTime(): void
    {
        $fixture = [
            '2021-05-31T17:41:30+00:00',
            '2021-05-01T17:08:01+00:00',
            '2021-06-13T19:31:38+00:00',
            '2021-06-08T12:53:00+00:00',
            '2021-05-19T16:42:04+00:00',
            '2021-04-03T00:17:38+00:00',
            '2021-06-23T00:48:55+00:00',
            '2021-06-17T20:07:49+00:00',
            '2021-06-24T14:31:37+00:00',
            '2021-06-06T12:47:56+00:00',
        ];
        $this->sample10($fixture, function (Quatsch $gen) {
            return $gen->datetime('2021-04-01', '2021-07-01')->format('c');
        });
    }

    /** @small */
    public function testArrayKey(): void
    {
        $input = ['a' => 3, 'b' => 5, 'c' => 7];
        $fixture = ['c', 'b', 'c', 'c', 'b', 'a', 'c', 'c', 'c', 'c'];
        $this->sample10($fixture, function (Quatsch $gen) use ($input) {
            return $gen->arrayKey($input);
        });
    }

    /** @small */
    public function testArrayValue(): void
    {
        $input = ['a' => 3, 'b' => 5, 'c' => 7];
        $fixture = [7, 5, 7, 7, 5, 3, 7, 7, 7, 7];
        $this->sample10($fixture, function (Quatsch $gen) use ($input) {
            return $gen->arrayValue($input);
        });
    }

    /** @small */
    public function testWords(): void
    {
        $gen = self::newInstance();
        $fixture = implode("\n\n", [
            'Dictum eu justo tincidunt nostra facilisis litora quisque nunc, arcu fermentum lacinia nibh dapibus volutpat nulla conubia curae, auctor pellentesque proin rhoncus sociosqu nascetur nisi. Sociosqu vulputate penatibus porta placerat felis enim aenean mollis eleifend, convallis erat platea varius taciti viverra fusce lacus condimentum pharetra, potenti auctor et dictum neque eu efficitur habitasse. Adipiscing sem mattis efficitur suscipit eget curabitur ligula montes, auctor eu condimentum bibendum penatibus quis leo, purus tortor torquent non lacus proin enim. Rutrum a velit class imperdiet gravida taciti auctor, curae pharetra magnis convallis suspendisse interdum at diam, vel est nec quisque habitasse senectus. Lacinia consequat aenean lobortis amet nec in a orci consectetur, etiam scelerisque aliquam purus curae rhoncus ad tortor viverra, gravida ex suspendisse efficitur sociosqu enim eu libero. Sed fringilla nec elementum penatibus finibus sodales curae, mus risus laoreet aliquet imperdiet augue. Habitasse justo sem per vestibulum nam volutpat est euismod maximus, himenaeos posuere arcu ullamcorper risus elementum ut lacinia, efficitur auctor dis habitant accumsan praesent ac molestie.',
            'Adipiscing blandit vitae posuere faucibus fames est maximus dictum condimentum mus, odio penatibus dignissim volutpat risus ultricies tortor egestas et senectus ante, eu tellus aenean rutrum consectetur ex morbi scelerisque consequat. Bibendum non hac proin ex iaculis condimentum donec netus aenean dignissim amet, taciti ullamcorper litora varius sollicitudin nulla libero imperdiet leo odio massa, curae habitant primis natoque ultrices commodo tempus mollis interdum felis. Vestibulum malesuada id lacus fermentum fringilla cras mollis inceptos augue tempor venenatis in, pretium ullamcorper viverra velit est eleifend platea eu feugiat vivamus. Leo augue nibh sociosqu volutpat praesent quam nisi sollicitudin habitasse, convallis ex lacinia himenaeos cursus turpis nascetur maecenas, porta fames commodo porttitor malesuada vulputate sem imperdiet. Nisl himenaeos massa libero risus taciti potenti vel facilisis posuere tortor molestie, lacinia fames mus quam pharetra pretium lorem bibendum venenatis nisi, nunc scelerisque ultricies tristique elementum integer conubia vitae sapien arcu.',
        ]);
        $this->assertSame($fixture, $gen->paragraphs(1.2, 0.5));
    }

    /** @medium */
    public function testLongRun(): void
    {
        $text = self::newInstance()->paragraphs(200);
        $this->assertSame(209103, strlen($text));
    }

    /**
     * @param mixed[] $fixture
     */
    private function sample10(array $fixture, callable $sampler): void
    {
        $gen = self::newInstance();

        $samples = [];
        for ($i = 0; $i < 10; $i++) {
            $samples[] = $sampler($gen);
        }

        $this->assertSame($fixture, $samples);
    }

    private static function newInstance(): Quatsch
    {
        $gen = new Quatsch(47513);
        srand(); // sentinel
        return $gen;
    }
}
